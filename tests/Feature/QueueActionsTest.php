<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\QueueSchedule;
use App\Models\Resident;
use App\Models\RequestPurpose;
use App\Models\User;
use App\Services\WFQService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueueActionsTest extends TestCase
{
    use RefreshDatabase;

    private WFQService $wfq;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\ResidentCategorySeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\WFQConfigurationSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\QueueScheduleSeeder']);

        QueueSchedule::first()->update([
            'cut_off_time' => '00:00:00',
            'cut_off_enabled' => true,
        ]);

        Carbon::setTestNow('2026-09-10 10:00:00');

        $this->wfq = new WFQService;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeResident(): Resident
    {
        $user = User::factory()->create(['role' => 'resident']);

        return Resident::create([
            'user_id' => $user->id,
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'birthdate' => '1990-01-15',
            'age' => 35,
            'gender' => 'male',
            'nationality' => 'FILIPINO',
            'category' => 'regular',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
        ]);
    }

    private function makePersonnel(): User
    {
        $user = User::factory()->create(['role' => 'personnel']);
        $user->assignRole('personnel');

        return $user;
    }

    private function createPendingRequest(Resident $resident): DocumentRequest
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::first();

        $request = DocumentRequest::create([
            'control_number' => 'REQ-QA-'.Str::upper(Str::random(4)),
            'queue_number' => $this->wfq->generateQueueNumber(),
            'qr_code' => 'qrcodes/req-qa.png',
            'verification_token' => Str::random(32),
            'resident_id' => $resident->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->wfq->enqueue($request);

        return $request->refresh();
    }

    public function test_call_next_starts_review_and_assigns_personnel(): void
    {
        $personnel = $this->makePersonnel();
        $request = $this->createPendingRequest($this->makeResident());

        $this->actingAs($personnel)
            ->post(route('queue.call-next', $request->control_number))
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('reviewing', $request->status);
        $this->assertSame($personnel->id, $request->processed_by);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'call_next',
            'subject_type' => DocumentRequest::class,
            'subject_id' => $request->id,
        ]);
    }

    public function test_full_hold_resume_skip_ready_release_flow(): void
    {
        $personnel = $this->makePersonnel();
        $request = $this->createPendingRequest($this->makeResident());
        $url = route('queue.call-next', $request->control_number);
        $base = $request->control_number;

        $this->actingAs($personnel)->post(route('queue.call-next', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('reviewing', $request->status);

        $this->actingAs($personnel)->post(route('queue.hold', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('on_hold', $request->status);

        $this->actingAs($personnel)->post(route('queue.resume', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('pending', $request->status);
        $this->assertNotNull($request->virtual_finish_time);

        $this->actingAs($personnel)->post(route('queue.skip', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('pending', $request->status);
        $this->assertSame('2026-09-11', $request->service_date->toDateString());
        $this->assertTrue($request->scheduled_after_cutoff);
        $this->assertDatabaseHas('audit_logs', ['action' => 'skip', 'subject_id' => $request->id]);

        $this->actingAs($personnel)->post(route('queue.call-next', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('reviewing', $request->status);

        $this->actingAs($personnel)->post(route('queue.ready', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('ready_for_release', $request->status);

        $this->actingAs($personnel)->post(route('queue.release', $base))->assertRedirect();
        $request->refresh();
        $this->assertSame('released', $request->status);
        $this->assertNotNull($request->completed_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'release', 'subject_id' => $request->id]);
    }

    public function test_release_requires_ready_for_release_status(): void
    {
        $personnel = $this->makePersonnel();
        $request = $this->createPendingRequest($this->makeResident());

        $this->actingAs($personnel)
            ->post(route('queue.release', $request->control_number))
            ->assertRedirect()
            ->assertSessionHasErrors('queue');

        $this->assertSame('pending', $request->refresh()->status);
    }

    public function test_mark_ready_requires_reviewing_status(): void
    {
        $personnel = $this->makePersonnel();
        $request = $this->createPendingRequest($this->makeResident());

        $this->actingAs($personnel)
            ->post(route('queue.ready', $request->control_number))
            ->assertRedirect()
            ->assertSessionHasErrors('queue');

        $this->assertSame('pending', $request->refresh()->status);
    }

    public function test_call_next_rejects_on_hold_request(): void
    {
        $personnel = $this->makePersonnel();
        $request = $this->createPendingRequest($this->makeResident());

        $this->actingAs($personnel)
            ->post(route('queue.hold', $request->control_number))
            ->assertRedirect();

        $this->actingAs($personnel)
            ->post(route('queue.call-next', $request->control_number))
            ->assertRedirect()
            ->assertSessionHasErrors('queue');

        $this->assertSame('on_hold', $request->refresh()->status);
    }

    public function test_actions_are_logged_through_audit_trail(): void
    {
        $personnel = $this->makePersonnel();
        $request = $this->createPendingRequest($this->makeResident());

        $this->actingAs($personnel)->post(route('queue.call-next', $request->control_number))->assertRedirect();
        $this->actingAs($personnel)->post(route('queue.hold', $request->control_number))->assertRedirect();
        $this->actingAs($personnel)->post(route('queue.resume', $request->control_number))->assertRedirect();

        $actions = AuditLog::where('subject_type', DocumentRequest::class)
            ->where('subject_id', $request->id)
            ->pluck('action')
            ->all();

        $this->assertContains('call_next', $actions);
        $this->assertContains('hold', $actions);
        $this->assertContains('resume', $actions);
    }
}