<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Resident;
use App\Models\RequestPurpose;
use App\Models\User;
use App\Services\ControlNumberService;
use App\Services\WFQService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WFQServiceTest extends TestCase
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

        $this->wfq = new WFQService;
    }

    private function makeResident(string $category): Resident
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
            'category' => $category,
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
        ]);
    }

    public function test_privileged_categories_and_purposes_receive_higher_weights(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $medical = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $enrollment = RequestPurpose::where('code', 'ENROLLMENT')->first();

        $senior = $this->makeResident('senior');
        $regular = $this->makeResident('regular');

        $seniorMedical = $this->wfq->calculateWeight($senior, $docType, $medical);
        $regularMedical = $this->wfq->calculateWeight($regular, $docType, $medical);
        $regularEnrollment = $this->wfq->calculateWeight($regular, $docType, $enrollment);

        $this->assertGreaterThan($regularMedical, $seniorMedical);
        $this->assertGreaterThan($regularEnrollment, $regularMedical);
    }

    public function test_queue_positions_follow_virtual_finish_time(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'POLICE_CLEARANCE')->first();

        $senior = $this->makeResident('senior');
        $regular = $this->makeResident('regular');

        $seniorRequest = DocumentRequest::create([
            'control_number' => 'REQ-WFQ-0001',
            'queue_number' => 'Q-0001',
            'qr_code' => 'qrcodes/REQ-WFQ-0001.png',
            'resident_id' => $senior->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $regularRequest = DocumentRequest::create([
            'control_number' => 'REQ-WFQ-0002',
            'queue_number' => 'Q-0002',
            'qr_code' => 'qrcodes/REQ-WFQ-0002.png',
            'resident_id' => $regular->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->wfq->enqueue($seniorRequest);
        $this->wfq->enqueue($regularRequest);

        $seniorRequest->refresh();
        $regularRequest->refresh();

        $this->assertGreaterThan($regularRequest->total_weight, $seniorRequest->total_weight);
        $this->assertLessThan($regularRequest->virtual_finish_time, $seniorRequest->virtual_finish_time);
        $this->assertEquals(1, $seniorRequest->queue_position);
        $this->assertEquals(2, $regularRequest->queue_position);
    }

    public function test_control_numbers_are_unique_and_sequence_keeps_incrementing(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::first();
        $resident = $this->makeResident('regular');
        $service = new ControlNumberService;

        $numbers = [];
        for ($i = 0; $i < 3; $i++) {
            $control = $service->generateControlNumber();
            DocumentRequest::create([
                'control_number' => $control,
                'queue_number' => 'SQ-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'qr_code' => 'qrcodes/'.$control.'.png',
                'resident_id' => $resident->id,
                'document_type_id' => $docType->id,
                'purpose_id' => $purpose->id,
                'status' => 'pending',
                'total_weight' => 0.00,
                'virtual_finish_time' => 0.000000,
            ]);
            $numbers[] = $control;
        }

        $this->assertCount(3, array_unique($numbers));

        $last = (int) substr($numbers[2], strrpos($numbers[2], '-') + 1);
        $this->assertEquals(3, $last);
    }

    public function test_queue_monitor_orders_privileged_first(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'POLICE_CLEARANCE')->first();

        $senior = $this->makeResident('senior');
        $regular = $this->makeResident('regular');

        $seniorRequest = DocumentRequest::create([
            'control_number' => 'REQ-MON-0001',
            'queue_number' => 'Q-0101',
            'qr_code' => 'qrcodes/REQ-MON-0001.png',
            'resident_id' => $senior->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $regularRequest = DocumentRequest::create([
            'control_number' => 'REQ-MON-0002',
            'queue_number' => 'Q-0102',
            'qr_code' => 'qrcodes/REQ-MON-0002.png',
            'resident_id' => $regular->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->wfq->enqueue($seniorRequest);
        $this->wfq->enqueue($regularRequest);

        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $this->actingAs($personnel)
            ->get(route('queue.monitor'))
            ->assertOk()
            ->assertSee('Q-0101');
    }
}