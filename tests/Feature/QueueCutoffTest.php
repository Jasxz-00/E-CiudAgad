<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Models\QueueSchedule;
use App\Models\QueueScheduleHoliday;
use App\Models\User;
use App\Services\QueueScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class QueueCutoffTest extends TestCase
{
    use RefreshDatabase;

    private QueueScheduleService $scheduleService;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\ResidentCategorySeeder']);

        $this->scheduleService = new QueueScheduleService;

        QueueSchedule::create([
            'cut_off_time' => '16:00:00',
            'opening_time' => '08:00:00',
            'max_requests_per_day' => null,
            'cut_off_enabled' => true,
            'is_active' => true,
            'timezone' => 'Asia/Manila',
        ]);
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

    public function test_before_cutoff_schedules_current_day(): void
    {
        Carbon::setTestNow('2026-09-10 09:00:00');

        $assignment = $this->scheduleService->assignServiceDate();

        $this->assertSame('2026-09-10', $assignment['service_date']);
        $this->assertFalse($assignment['scheduled_after_cutoff']);
    }

    public function test_after_cutoff_schedules_next_working_day(): void
    {
        Carbon::setTestNow('2026-09-10 17:00:00');

        $assignment = $this->scheduleService->assignServiceDate();

        $this->assertSame('2026-09-11', $assignment['service_date']);
        $this->assertTrue($assignment['scheduled_after_cutoff']);
    }

    public function test_weekend_submission_schedules_monday(): void
    {
        Carbon::setTestNow('2026-09-12 10:00:00');

        $assignment = $this->scheduleService->assignServiceDate();

        $this->assertSame('2026-09-14', $assignment['service_date']);
        $this->assertTrue($assignment['scheduled_after_cutoff']);
    }

    public function test_holiday_is_skipped(): void
    {
        $schedule = QueueSchedule::first();

        QueueScheduleHoliday::create([
            'queue_schedule_id' => $schedule->id,
            'holiday_date' => '2026-09-11',
            'name' => 'Test Holiday',
            'is_active' => true,
        ]);

        Carbon::setTestNow('2026-09-10 17:00:00');

        $assignment = $this->scheduleService->assignServiceDate();

        $this->assertSame('2026-09-14', $assignment['service_date']);
        $this->assertTrue($assignment['scheduled_after_cutoff']);
    }

    public function test_full_capacity_forces_next_working_day(): void
    {
        QueueSchedule::query()->update(['max_requests_per_day' => 1]);

        $resident = $this->makeResident();

        DocumentRequest::create([
            'control_number' => 'REQ-CUT-0001',
            'queue_number' => 'Q-2001',
            'qr_code' => 'qrcodes/REQ-CUT-0001.png',
            'resident_id' => $resident->id,
            'document_type_id' => 1,
            'purpose_id' => 1,
            'status' => 'pending',
            'service_date' => '2026-09-10',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        Carbon::setTestNow('2026-09-10 09:00:00');

        $assignment = $this->scheduleService->assignServiceDate();

        $this->assertSame('2026-09-11', $assignment['service_date']);
        $this->assertTrue($assignment['scheduled_after_cutoff']);
    }

    public function test_disabled_cutoff_always_schedules_current_day(): void
    {
        QueueSchedule::query()->update(['cut_off_enabled' => false]);

        Carbon::setTestNow('2026-09-12 17:00:00');

        $assignment = $this->scheduleService->assignServiceDate();

        $this->assertSame('2026-09-12', $assignment['service_date']);
        $this->assertFalse($assignment['scheduled_after_cutoff']);
    }
}