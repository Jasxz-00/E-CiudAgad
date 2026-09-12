<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class QRCodeVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    private function makeResidentUser(): User
    {
        $user = User::factory()->create(['role' => 'resident']);
        $user->assignRole('resident');

        return $user;
    }

    private function makePersonnel(): User
    {
        $user = User::factory()->create(['role' => 'personnel']);
        $user->assignRole('personnel');

        return $user;
    }

    private function makeResident(): Resident
    {
        $user = $this->makeResidentUser();

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

    public function test_verification_page_requires_authentication(): void
    {
        $token = Str::random(32);

        $this->get(route('verification.show', ['token' => $token]))
            ->assertRedirect(route('login'));
    }

    public function test_owner_resident_can_verify_own_request(): void
    {
        $resident = $this->makeResident();
        $token = Str::random(32);

        DocumentRequest::create([
            'control_number' => 'REQ-QR-0001',
            'queue_number' => 'Q-3001',
            'qr_code' => 'qrcodes/'.$token.'.png',
            'verification_token' => $token,
            'resident_id' => $resident->id,
            'document_type_id' => 1,
            'purpose_id' => 1,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->actingAs($resident->user)
            ->get(route('verification.show', ['token' => $token]))
            ->assertOk()
            ->assertSee('Q-3001');
    }

    public function test_other_resident_cannot_verify_someone_elses_request(): void
    {
        $owner = $this->makeResident();
        $other = $this->makeResident();
        $token = Str::random(32);

        DocumentRequest::create([
            'control_number' => 'REQ-QR-0002',
            'queue_number' => 'Q-3002',
            'qr_code' => 'qrcodes/'.$token.'.png',
            'verification_token' => $token,
            'resident_id' => $owner->id,
            'document_type_id' => 1,
            'purpose_id' => 1,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->actingAs($other->user)
            ->get(route('verification.show', ['token' => $token]))
            ->assertForbidden();
    }

    public function test_personnel_can_verify_any_request(): void
    {
        $resident = $this->makeResident();
        $personnel = $this->makePersonnel();
        $token = Str::random(32);

        DocumentRequest::create([
            'control_number' => 'REQ-QR-0003',
            'queue_number' => 'Q-3003',
            'qr_code' => 'qrcodes/'.$token.'.png',
            'verification_token' => $token,
            'resident_id' => $resident->id,
            'document_type_id' => 1,
            'purpose_id' => 1,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->actingAs($personnel)
            ->get(route('verification.show', ['token' => $token]))
            ->assertOk()
            ->assertSee('Q-3003');
    }

    public function test_submission_persists_token_service_date_and_qr_path(): void
    {
        $resident = $this->makeResident();

        $this->actingAs($resident->user)
            ->post(route('resident.submit-request'), [
                'document_type_id' => 1,
                'purpose_id' => 1,
                'purpose_other' => '',
            ])
            ->assertRedirect(route('resident.requests'));

        $request = DocumentRequest::where('resident_id', $resident->id)->first();

        $this->assertNotNull($request);
        $this->assertNotNull($request->verification_token);
        $this->assertSame(32, strlen($request->verification_token));
        $this->assertSame(now()->toDateString(), $request->service_date->toDateString());
        $this->assertStringStartsWith('qrcodes/', $request->qr_code);
        $this->assertStringEndsWith('.png', $request->qr_code);

        $this->actingAs($resident->user)
            ->get(route('verification.show', ['token' => $request->verification_token]))
            ->assertOk();
    }
}