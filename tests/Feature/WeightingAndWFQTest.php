<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Resident;
use App\Models\RequestPurpose;
use App\Models\User;
use App\Services\WFQService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WeightingAndWFQTest extends TestCase
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

    public function test_weight_uses_addition_formula(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $proof = RequestPurpose::where('code', 'PROOF_OF_ADDRESS')->first();
        $medical = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');

        $this->assertEquals(2, $this->wfq->calculateWeight($regular, $proof));
        $this->assertEquals(6, $this->wfq->calculateWeight($senior, $medical));
    }

    public function test_unknown_purpose_falls_back_to_default_weight(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $postalId = RequestPurpose::where('code', 'POSTAL_ID')->first();
        $regular = $this->makeResident('regular');

        $this->assertEquals(1, $this->wfq->purposeWeight($postalId));
        $this->assertEquals(2, $this->wfq->calculateWeight($regular, $postalId));
    }

    public function test_virtual_finish_time_is_isolated_per_service_date(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $proof = RequestPurpose::where('code', 'PROOF_OF_ADDRESS')->first();
        $medical = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');

        $todayRegular = DocumentRequest::create([
            'control_number' => 'REQ-WGT-0001',
            'queue_number' => 'Q-1001',
            'qr_code' => 'qrcodes/REQ-WGT-0001.png',
            'resident_id' => $regular->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $proof->id,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $tomorrowSenior = DocumentRequest::create([
            'control_number' => 'REQ-WGT-0002',
            'queue_number' => 'Q-1002',
            'qr_code' => 'qrcodes/REQ-WGT-0002.png',
            'resident_id' => $senior->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $medical->id,
            'status' => 'pending',
            'service_date' => now()->addDay()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->wfq->enqueue($todayRegular);
        $this->wfq->enqueue($tomorrowSenior);

        $todayRegular->refresh();
        $tomorrowSenior->refresh();

        $this->assertEqualsWithDelta(0.5, $todayRegular->virtual_finish_time, 0.0001);
        $this->assertEqualsWithDelta(1 / 6, $tomorrowSenior->virtual_finish_time, 0.0001);
        $this->assertEquals(1, $todayRegular->queue_position);
        $this->assertEquals(1, $tomorrowSenior->queue_position);
    }

    public function test_queue_number_is_globally_unique(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::first();

        $numbers = [];
        for ($i = 0; $i < 3; $i++) {
            $resident = $this->makeResident('regular');
            $numbers[] = $this->wfq->generateQueueNumber();
            DocumentRequest::create([
                'control_number' => 'REQ-WGT-00'.($i + 3),
                'queue_number' => $numbers[$i],
                'qr_code' => 'qrcodes/x'.($i + 3).'.png',
                'resident_id' => $resident->id,
                'document_type_id' => $docType->id,
                'purpose_id' => $purpose->id,
                'status' => 'pending',
                'service_date' => now()->toDateString(),
                'total_weight' => 0.00,
                'virtual_finish_time' => 0.000000,
            ]);
        }

        $this->assertCount(3, array_unique($numbers));
        $this->assertSame(['Q-0001', 'Q-0002', 'Q-0003'], $numbers);
    }

    public function test_recalculate_reorders_queue_positions_by_virtual_finish_time(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $proof = RequestPurpose::where('code', 'PROOF_OF_ADDRESS')->first();

        $senior = $this->makeResident('senior');
        $pwd = $this->makeResident('pwd');
        $regular = $this->makeResident('regular');

        $seniorRequest = DocumentRequest::create([
            'control_number' => 'REQ-WGT-0006',
            'queue_number' => 'Q-1006',
            'qr_code' => 'qrcodes/REQ-WGT-0006.png',
            'resident_id' => $senior->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $proof->id,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);
        $pwdRequest = DocumentRequest::create([
            'control_number' => 'REQ-WGT-0007',
            'queue_number' => 'Q-1007',
            'qr_code' => 'qrcodes/REQ-WGT-0007.png',
            'resident_id' => $pwd->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $proof->id,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);
        $regularRequest = DocumentRequest::create([
            'control_number' => 'REQ-WGT-0008',
            'queue_number' => 'Q-1008',
            'qr_code' => 'qrcodes/REQ-WGT-0008.png',
            'resident_id' => $regular->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $proof->id,
            'status' => 'pending',
            'service_date' => now()->toDateString(),
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->wfq->enqueue($pwdRequest);
        $this->wfq->enqueue($regularRequest);

        $pwdRequest->refresh();
        $regularRequest->refresh();

        $seniorRequest->refresh();
        $seniorRequest->update(['virtual_finish_time' => 0.1]);

        $this->wfq->recalculateQueue();

        $this->assertEquals(1, DocumentRequest::find($seniorRequest->id)->queue_position);
        $this->assertEquals(2, DocumentRequest::find($pwdRequest->id)->queue_position);
        $this->assertEquals(3, DocumentRequest::find($regularRequest->id)->queue_position);
    }
}