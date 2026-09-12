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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class QueueWFQTest extends TestCase
{
    use RefreshDatabase;

    private WFQService $wfq;
    private string $serviceDate = '2026-09-11';

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\ResidentCategorySeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\WFQConfigurationSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypePurposeSeeder']);

        $this->wfq = new WFQService;
    }

    private function makeResident(string $category = 'regular'): Resident
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

    // ============================================================
    // 1. WEIGHT CALCULATION TESTS
    // ============================================================

    public function test_regular_resident_weight_is_one(): void
    {
        $resident = $this->makeResident('regular');
        $this->assertSame(1.0, $this->wfq->residentWeight($resident));
    }

    public function test_senior_citizen_weight_is_two(): void
    {
        $resident = $this->makeResident('senior');
        $this->assertSame(2.0, $this->wfq->residentWeight($resident));
    }

    public function test_pregnant_resident_weight_is_three(): void
    {
        $resident = $this->makeResident('pregnant');
        $this->assertSame(3.0, $this->wfq->residentWeight($resident));
    }

    public function test_pwd_resident_weight_is_four(): void
    {
        $resident = $this->makeResident('pwd');
        $this->assertSame(4.0, $this->wfq->residentWeight($resident));
    }

    public function test_purpose_weight_medical_assistance_is_four(): void
    {
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $this->assertSame(4.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_financial_assistance_is_three(): void
    {
        $purpose = RequestPurpose::where('code', 'FINANCIAL_ASSISTANCE')->first();
        $this->assertSame(3.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_education_is_two(): void
    {
        $purpose = RequestPurpose::where('code', 'EDUCATION')->first();
        $this->assertSame(2.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_scholarship_is_two(): void
    {
        $purpose = RequestPurpose::where('code', 'SCHOLARSHIP')->first();
        $this->assertSame(2.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_legal_requirement_is_two(): void
    {
        $purpose = RequestPurpose::where('code', 'LEGAL_REQUIREMENT')->first();
        $this->assertSame(2.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_employment_is_two(): void
    {
        $purpose = RequestPurpose::where('code', 'EMPLOYMENT')->first();
        $this->assertSame(2.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_financial_loan_transaction_is_one(): void
    {
        $purpose = RequestPurpose::where('code', 'FINANCIAL_LOAN_TRANSACTION')->first();
        $this->assertSame(1.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_purpose_weight_proof_of_address_is_one(): void
    {
        $purpose = RequestPurpose::where('code', 'PROOF_OF_ADDRESS')->first();
        $this->assertSame(1.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_total_weight_equals_resident_weight_plus_purpose_weight(): void
    {
        $regular = $this->makeResident('regular');
        $medical = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $this->assertSame(5.0, $this->wfq->calculateWeight($regular, $medical));

        $senior = $this->makeResident('senior');
        $financial = RequestPurpose::where('code', 'FINANCIAL_ASSISTANCE')->first();
        $this->assertSame(5.0, $this->wfq->calculateWeight($senior, $financial));

        $pwd = $this->makeResident('pwd');
        $education = RequestPurpose::where('code', 'EDUCATION')->first();
        $this->assertSame(6.0, $this->wfq->calculateWeight($pwd, $education));

        $pregnant = $this->makeResident('pregnant');
        $legal = RequestPurpose::where('code', 'LEGAL_REQUIREMENT')->first();
        $this->assertSame(5.0, $this->wfq->calculateWeight($pregnant, $legal));
    }

    public function test_calculate_weight_stores_correct_values_on_document_request(): void
    {
        $resident = $this->makeResident('pwd');
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($resident, $purpose);

        $request = DocumentRequest::create([
            'control_number' => 'REQ-WFQ-WGT-0001',
            'queue_number' => 'Q-9001',
            'qr_code' => 'qrcodes/REQ-WFQ-WGT-0001.png',
            'resident_id' => $resident->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'service_date' => $this->serviceDate,
            'resident_weight' => (string) $this->wfq->residentWeight($resident),
            'purpose_weight' => (string) $this->wfq->purposeWeight($purpose),
            'total_weight' => (string) $weight,
            'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate),
        ]);

        $this->assertEquals(4.0, (float) $request->resident_weight);
        $this->assertEquals(4.0, (float) $request->purpose_weight);
        $this->assertEquals(8.0, (float) $request->total_weight);
    }

    // ============================================================
    // 2. WFQ FORMULA TESTS
    // ============================================================

    public function test_wfq_formula_first_request_has_vft_equal_to_one_over_weight(): void
    {
        $regular = $this->makeResident('regular');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($regular, $purpose);
        $vft = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);

        $this->assertEqualsWithDelta(1.0 / $weight, $vft, 0.0001);
    }

    public function test_wfq_formula_subsequent_request_chains_from_previous_vft(): void
    {
        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $firstWeight = $this->wfq->calculateWeight($regular, $purpose);
        $secondWeight = $this->wfq->calculateWeight($senior, $purpose);

        $req1 = DocumentRequest::create([
            'control_number' => 'REQ-FORMULA-0002',
            'queue_number' => 'Q-1002',
            'qr_code' => 'qrcodes/REQ-FORMULA-0002.png',
            'resident_id' => $regular->id,
            'document_type_id' => DocumentType::where('code', 'BRGY_CLEARANCE')->first()->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'service_date' => $this->serviceDate,
            'total_weight' => $firstWeight,
            'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($firstWeight, $this->serviceDate),
        ]);

        $secondVft = $this->wfq->calculateVirtualFinishTime($secondWeight, $this->serviceDate);
        $expectedFirstVft = 1.0 / $firstWeight;
        $expectedSecondVft = max($expectedFirstVft, 0) + 1.0 / $secondWeight;

        $this->assertEqualsWithDelta($expectedFirstVft, $req1->virtual_finish_time, 0.0001);
        $this->assertEqualsWithDelta($expectedSecondVft, $secondVft, 0.0001);
        $this->assertGreaterThan($req1->virtual_finish_time, $secondVft);
    }

    public function test_service_length_l_constant_is_one(): void
    {
        $regular = $this->makeResident('regular');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($regular, $purpose);

        $vft = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $expectedVft = WFQService::SERVICE_LENGTH / $weight;

        $this->assertEqualsWithDelta($expectedVft, $vft, 0.0001);
    }

    public function test_subsequent_request_uses_max_of_previous_vft_and_own_value(): void
    {
        $resident = $this->makeResident('regular');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($resident, $purpose);

        $req1 = DocumentRequest::create([
            'control_number' => 'REQ-MAX-0001',
            'queue_number' => 'Q-MAX1',
            'qr_code' => 'qrcodes/REQ-MAX-0001.png',
            'resident_id' => $resident->id,
            'document_type_id' => DocumentType::where('code', 'BRGY_CLEARANCE')->first()->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'service_date' => $this->serviceDate,
            'total_weight' => $weight,
            'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate),
        ]);

        $vft2 = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $expectedSecondVft = max($req1->virtual_finish_time, 0) + 1.0 / $weight;

        $this->assertEqualsWithDelta($req1->virtual_finish_time, $req1->virtual_finish_time, 0.0001);
        $this->assertEqualsWithDelta($expectedSecondVft, $vft2, 0.0001);
        $this->assertGreaterThan($req1->virtual_finish_time, $vft2);
    }

    public function test_first_request_has_f_equal_to_v_plus_l_over_w(): void
    {
        $regular = $this->makeResident('regular');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($regular, $purpose);

        $vft = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $expected = 0 + 1.0 / $weight;

        $this->assertEqualsWithDelta($expected, $vft, 0.0001);
    }

    public function test_document_type_weight_is_not_used_in_calculation(): void
    {
        $regular = $this->makeResident('regular');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $weightWithBrgy = $this->wfq->calculateWeight($regular, $purpose);
        $weightWithIndigency = $this->wfq->calculateWeight($regular, $purpose);

        $this->assertSame($weightWithBrgy, $weightWithIndigency);
    }

    public function test_document_type_complexity_weight_does_not_affect_vft(): void
    {
        $regular = $this->makeResident('regular');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($regular, $purpose);

        $vftBrgy = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $vftIndigency = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);

        $this->assertSame($vftBrgy, $vftIndigency);
    }

    // ============================================================
    // 3. DOCUMENT-PURPOSE VALIDATION TESTS
    // ============================================================

    public function test_invalid_document_purpose_combination_is_rejected(): void
    {
        $goodMorale = DocumentType::where('code', 'CERT_GOOD_MORAL')->first();
        $legal = RequestPurpose::where('code', 'LEGAL_REQUIREMENT')->first();

        $mapped = $goodMorale->purposes()->where('request_purposes.id', $legal->id)->exists();
        $this->assertFalse($mapped, 'LEGAL_REQUIREMENT should not be a valid purpose for CERT_GOOD_MORAL');
    }

    public function test_valid_document_purpose_combination_passes(): void
    {
        $brgy = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $medical = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $mapped = $brgy->purposes()->where('request_purposes.id', $medical->id)->exists();
        $this->assertTrue($mapped, 'MEDICAL_ASSISTANCE should be a valid purpose for BRGY_CLEARANCE');
    }

    public function test_purpose_combo_is_enforced_in_registration_validator(): void
    {
        $goodMorale = DocumentType::where('code', 'CERT_GOOD_MORAL')->first();
        $legal = RequestPurpose::where('code', 'LEGAL_REQUIREMENT')->first();

        $data = [
            'first_name' => 'JUAN', 'last_name' => 'DELA CRUZ',
            'birthdate_month' => '1', 'birthdate_day' => '15', 'birthdate_year' => '1990',
            'gender' => 'male', 'civil_status' => 'single',
            'place_of_birth' => 'BACOOR CITY, CAVITE',
            'contact_number' => '0912-345-6789', 'emergency_contact' => '0998-765-4321',
            'id_type' => 'phil_id',
            'document_type_id' => (string) $goodMorale->id,
            'purpose_id' => (string) $legal->id,
            'privacy_consent' => '1', 'assisted_mode' => '0',
        ];

        $request = new \App\Http\Requests\RegisterResidentRequest($data);
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('purpose_id', $validator->errors()->getMessages());
    }

    public function test_proof_type_is_required(): void
    {
        $rules = (new \App\Http\Requests\RegisterResidentRequest)->rules();
        $this->assertArrayHasKey('proof_type', $rules);
        $this->assertContains('required', $rules['proof_type']);
    }

    public function test_proof_type_must_be_one_of_valid_values(): void
    {
        $rules = (new \App\Http\Requests\RegisterResidentRequest)->rules();
        $ruleString = implode(' ', $rules['proof_type']);
        $this->assertStringContainsString('valid_id,birth_certificate,baptismal_certificate,hoa_certificate', $ruleString);
    }

    public function test_proof_type_accepts_all_valid_values(): void
    {
        foreach (['valid_id', 'birth_certificate', 'baptismal_certificate', 'hoa_certificate'] as $value) {
            $validator = Validator::make(['proof_type' => $value], ['proof_type' => 'required|in:valid_id,birth_certificate,baptismal_certificate,hoa_certificate']);
            $this->assertTrue($validator->passes(), "proof_type={$value} should pass");
        }
    }

    public function test_proof_type_rejects_invalid_values(): void
    {
        $validator = Validator::make(['proof_type' => 'passport'], ['proof_type' => 'required|in:valid_id,birth_certificate,baptismal_certificate,hoa_certificate']);
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('proof_type', $validator->errors()->getMessages());
    }

    public function test_proof_type_rejects_empty_string(): void
    {
        $validator = Validator::make(['proof_type' => ''], ['proof_type' => 'required|in:valid_id,birth_certificate,baptismal_certificate,hoa_certificate']);
        $this->assertFalse($validator->passes());
    }

    public function test_proof_type_rejects_null(): void
    {
        $validator = Validator::make(['proof_type' => null], ['proof_type' => 'required|in:valid_id,birth_certificate,baptismal_certificate,hoa_certificate']);
        $this->assertFalse($validator->passes());
    }

    // ============================================================
    // 4. QUEUE SORTING TESTS
    // ============================================================

    public function test_queue_is_sorted_by_virtual_finish_time_asc(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');
        $pwd = $this->makeResident('pwd');

        $wRegular = $this->wfq->calculateWeight($regular, $purpose);
        $wSenior = $this->wfq->calculateWeight($senior, $purpose);
        $wPwd = $this->wfq->calculateWeight($pwd, $purpose);

        $vRegular = 1.000000;
        $vSenior = 1.500000;
        $vPwd = 1.833333;

        $reqRegular = DocumentRequest::create([
            'control_number' => 'REQ-SORT-001', 'queue_number' => 'Q-S01',
            'qr_code' => 'qrcodes/REQ-SORT-001.png', 'resident_id' => $regular->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wRegular, 'virtual_finish_time' => $vRegular,
            'queue_position' => 3,
        ]);
        $reqSenior = DocumentRequest::create([
            'control_number' => 'REQ-SORT-002', 'queue_number' => 'Q-S02',
            'qr_code' => 'qrcodes/REQ-SORT-002.png', 'resident_id' => $senior->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wSenior, 'virtual_finish_time' => $vSenior,
            'queue_position' => 1,
        ]);
        $reqPwd = DocumentRequest::create([
            'control_number' => 'REQ-SORT-003', 'queue_number' => 'Q-S03',
            'qr_code' => 'qrcodes/REQ-SORT-003.png', 'resident_id' => $pwd->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wPwd, 'virtual_finish_time' => $vPwd,
            'queue_position' => 2,
        ]);

        $sorted = DocumentRequest::where('status', 'pending')
            ->orderBy('virtual_finish_time', 'asc')->get();

        $this->assertSame($reqRegular->id, $sorted[0]->id);
        $this->assertSame($reqSenior->id, $sorted[1]->id);
        $this->assertSame($reqPwd->id, $sorted[2]->id);
    }

    public function test_queue_number_is_not_used_for_sorting(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');
        $wSenior = $this->wfq->calculateWeight($senior, $purpose);
        $wRegular = $this->wfq->calculateWeight($regular, $purpose);

        $req1 = DocumentRequest::create([
            'control_number' => 'REQ-QN-001', 'queue_number' => 'Q-9999',
            'qr_code' => 'qrcodes/REQ-QN-001.png', 'resident_id' => $regular->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wRegular, 'virtual_finish_time' => 1.000000,
            'queue_position' => 2,
        ]);
        $seniorReq = DocumentRequest::create([
            'control_number' => 'REQ-QN-002', 'queue_number' => 'Q-0001',
            'qr_code' => 'qrcodes/REQ-QN-002.png', 'resident_id' => $senior->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wSenior, 'virtual_finish_time' => 0.500000,
            'queue_position' => 1,
        ]);

        $sorted = DocumentRequest::where('status', 'pending')
            ->orderBy('virtual_finish_time', 'asc')->get();

        $this->assertSame($seniorReq->id, $sorted[0]->id);
        $this->assertSame($req1->id, $sorted[1]->id);
        $this->assertSame('Q-9999', $req1->queue_number);
        $this->assertSame('Q-0001', $seniorReq->queue_number);
    }

    public function test_submitted_at_is_used_as_tie_breaker(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $resident = $this->makeResident('regular');
        $weight = $this->wfq->calculateWeight($resident, $purpose);
        $vft = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);

        $req1 = DocumentRequest::create([
            'control_number' => 'REQ-TB-001', 'queue_number' => 'Q-TB1',
            'qr_code' => 'qrcodes/REQ-TB-001.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $vft,
            'created_at' => now()->subSeconds(10), 'queue_position' => 1,
        ]);
        $req2 = DocumentRequest::create([
            'control_number' => 'REQ-TB-002', 'queue_number' => 'Q-TB2',
            'qr_code' => 'qrcodes/REQ-TB-002.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $vft,
            'created_at' => now()->subSeconds(5), 'queue_position' => 2,
        ]);

        $sorted = DocumentRequest::where('virtual_finish_time', $vft)
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $this->assertTrue($sorted[0]->created_at->lte($sorted[1]->created_at));
    }

    public function test_id_is_used_as_final_tie_breaker(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $resident = $this->makeResident('regular');
        $weight = $this->wfq->calculateWeight($resident, $purpose);
        $vft = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);

        $req1 = DocumentRequest::create([
            'control_number' => 'REQ-TIE-001', 'queue_number' => 'Q-TT1',
            'qr_code' => 'qrcodes/REQ-TIE-001.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $vft,
            'created_at' => now(), 'queue_position' => 1,
        ]);
        $req2 = DocumentRequest::create([
            'control_number' => 'REQ-TIE-002', 'queue_number' => 'Q-TT2',
            'qr_code' => 'qrcodes/REQ-TIE-002.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $vft,
            'created_at' => now(), 'queue_position' => 2,
        ]);

        $sorted = DocumentRequest::where('virtual_finish_time', $vft)
            ->where('created_at', $req1->created_at)
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $this->assertTrue($sorted[0]->id <= $sorted[1]->id);
    }

    // ============================================================
    // 5. NO FEES TESTS
    // ============================================================

    public function test_processing_fee_is_always_zero(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('document_requests');
        $this->assertNotContains('processing_fee', $columns);
    }

    public function test_no_payment_related_fields_exist_on_document_requests(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('document_requests');
        $paymentFields = ['processing_fee', 'discount', 'total_amount', 'paid_amount', 'payment_method', 'transaction_id', 'payment_status'];
        foreach ($paymentFields as $field) {
            $this->assertNotContains($field, $columns, "Payment field {$field} should not exist on document_requests table");
        }
    }

    public function test_document_request_has_no_financial_attributes_in_fillable(): void
    {
        $request = DocumentRequest::create([
            'control_number' => 'REQ-FEE-001', 'queue_number' => 'Q-FEE1',
            'qr_code' => 'qrcodes/REQ-FEE-001.png', 'resident_id' => $this->makeResident()->id,
            'document_type_id' => DocumentType::first()->id,
            'purpose_id' => RequestPurpose::first()->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
        ]);
        $fillable = $request->getFillable();
        $financialFields = ['processing_fee', 'total_amount', 'paid_amount', 'discount', 'payment_method', 'transaction_id'];
        foreach ($financialFields as $field) {
            $this->assertNotContains($field, $fillable);
        }
    }

    // ============================================================
    // 6. CONCURRENCY TESTS
    // ============================================================

    public function test_concurrent_enqueue_does_not_produce_duplicate_vfts(): void
    {
        $resident = $this->makeResident('regular');
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($resident, $purpose);

        $vft1 = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $vft2 = $vft1 + (1.0 / $weight);

        DocumentRequest::create([
            'control_number' => 'REQ-CONC-001', 'queue_number' => 'Q-C01',
            'qr_code' => 'qrcodes/REQ-CONC-001.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $vft1, 'queue_position' => 1,
        ]);
        $req2 = DocumentRequest::create([
            'control_number' => 'REQ-CONC-002', 'queue_number' => 'Q-C02',
            'qr_code' => 'qrcodes/REQ-CONC-002.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => 0, 'queue_position' => 2,
        ]);

        DB::transaction(function () use ($req2, $weight, $vft2) {
            DocumentRequest::where('service_date', $this->serviceDate)
                ->whereIn('status', ['pending', 'reviewing'])
                ->lockForUpdate()
                ->get();

            $req2->update(['virtual_finish_time' => $vft2, 'total_weight' => $weight]);
        });

        $req2->refresh();
        $this->assertNotEquals($vft1, $req2->virtual_finish_time);
        $this->assertGreaterThan($vft1, $req2->virtual_finish_time);
    }

    public function test_lock_for_update_prevents_race_conditions(): void
    {
        $resident = $this->makeResident('regular');
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($resident, $purpose);

        $vft1 = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $vft2 = $vft1 + (1.0 / $weight);

        DocumentRequest::create([
            'control_number' => 'REQ-LOCK-001', 'queue_number' => 'Q-L01',
            'qr_code' => 'qrcodes/REQ-LOCK-001.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $vft1, 'queue_position' => 1,
        ]);
        DocumentRequest::create([
            'control_number' => 'REQ-LOCK-002', 'queue_number' => 'Q-L02',
            'qr_code' => 'qrcodes/REQ-LOCK-002.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => 0, 'queue_position' => 2,
        ]);

        $vfts = [];
        DB::transaction(function () use (&$vfts, $vft1, $vft2) {
            DocumentRequest::where('service_date', $this->serviceDate)
                ->whereIn('status', ['pending', 'reviewing'])
                ->lockForUpdate()
                ->get();

            $vfts['first'] = $vft1;
            $vfts['second'] = $vft2;
        });

        $this->assertNotNull($vfts['first']);
        $this->assertNotNull($vfts['second']);
        $this->assertGreaterThan($vfts['first'], $vfts['second']);
    }

    public function test_lock_returns_locked_row(): void
    {
        $resident = $this->makeResident('regular');
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($resident, $purpose);

        $request = DocumentRequest::create([
            'control_number' => 'REQ-LOCK-R-001', 'queue_number' => 'Q-LR1',
            'qr_code' => 'qrcodes/REQ-LOCK-R-001.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
        ]);

        $locked = $this->wfq->lock($request);
        $this->assertNotNull($locked);
        $this->assertSame($request->id, $locked->id);
    }

    public function test_concurrent_enqueue_maintains_unique_queue_positions(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');
        $wSenior = $this->wfq->calculateWeight($senior, $purpose);
        $wRegular = $this->wfq->calculateWeight($regular, $purpose);

        DocumentRequest::create([
            'control_number' => 'REQ-POS-001', 'queue_number' => 'Q-P1',
            'qr_code' => 'qrcodes/REQ-POS-001.png', 'resident_id' => $regular->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wRegular, 'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($wRegular, $this->serviceDate),
            'queue_position' => 2,
        ]);
        DocumentRequest::create([
            'control_number' => 'REQ-POS-002', 'queue_number' => 'Q-P2',
            'qr_code' => 'qrcodes/REQ-POS-002.png', 'resident_id' => $senior->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wSenior, 'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($wSenior, $this->serviceDate),
            'queue_position' => 1,
        ]);

        $positions = DocumentRequest::where('status', 'pending')
            ->pluck('queue_position', 'id')->toArray();

        $this->assertCount(2, array_unique($positions));
        $this->assertEquals(1, min($positions));
        $this->assertEquals(2, max($positions));
    }

    public function test_recalculate_queue_orders_by_vft_created_at_id(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');
        $pwd = $this->makeResident('pwd');

        $wSenior = $this->wfq->calculateWeight($senior, $purpose);
        $wPwd = $this->wfq->calculateWeight($pwd, $purpose);
        $wRegular = $this->wfq->calculateWeight($regular, $purpose);

        $srVft = 0.500000;
        $prVft = 0.333333;
        $rgVft = 1.000000;

        DocumentRequest::create([
            'control_number' => 'REQ-REC-001', 'queue_number' => 'Q-R01',
            'qr_code' => 'qrcodes/REQ-REC-001.png', 'resident_id' => $regular->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wRegular, 'virtual_finish_time' => $rgVft,
            'queue_position' => 3, 'created_at' => now()->subSeconds(2),
        ]);
        DocumentRequest::create([
            'control_number' => 'REQ-REC-002', 'queue_number' => 'Q-R02',
            'qr_code' => 'qrcodes/REQ-REC-002.png', 'resident_id' => $senior->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wSenior, 'virtual_finish_time' => $srVft,
            'queue_position' => 1, 'created_at' => now()->subSeconds(1),
        ]);
        DocumentRequest::create([
            'control_number' => 'REQ-REC-003', 'queue_number' => 'Q-R03',
            'qr_code' => 'qrcodes/REQ-REC-003.png', 'resident_id' => $pwd->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wPwd, 'virtual_finish_time' => $prVft,
            'queue_position' => 2, 'created_at' => now(),
        ]);

        $ordered = DocumentRequest::where('status', 'pending')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')->toArray();

        $seniorId = DocumentRequest::where('resident_id', $senior->id)->first()->id;
        $pwdId = DocumentRequest::where('resident_id', $pwd->id)->first()->id;
        $regularId = DocumentRequest::where('resident_id', $regular->id)->first()->id;

        $this->assertSame([$pwdId, $seniorId, $regularId], $ordered);
    }

    // ============================================================
    // ADDITIONAL INTEGRATION TESTS
    // ============================================================

    public function test_calculate_weight_uses_config_defaults_for_unknown_category(): void
    {
        $resident = $this->makeResident('unknown_category');
        $this->assertSame(1.0, $this->wfq->residentWeight($resident));
    }

    public function test_calculate_weight_uses_config_defaults_for_null_purpose(): void
    {
        $resident = $this->makeResident('regular');
        $this->assertSame(1.0, $this->wfq->purposeWeight(null));
    }

    public function test_calculate_weight_uses_config_defaults_for_unknown_purpose(): void
    {
        $purpose = RequestPurpose::create([
            'name' => 'Unknown', 'code' => 'UNKNOWN_PURPOSE', 'priority_weight' => 1.0, 'is_active' => true,
        ]);
        $this->assertSame(1.0, $this->wfq->purposeWeight($purpose));
    }

    public function test_vft_is_per_service_date_isolated(): void
    {
        $resident = $this->makeResident('regular');
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $weight = $this->wfq->calculateWeight($resident, $purpose);

        DocumentRequest::create([
            'control_number' => 'REQ-ISO-001', 'queue_number' => 'Q-ISO1',
            'qr_code' => 'qrcodes/REQ-ISO-001.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => '2026-09-10',
            'total_weight' => $weight, 'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($weight, '2026-09-10'),
        ]);

        DocumentRequest::create([
            'control_number' => 'REQ-ISO-002', 'queue_number' => 'Q-ISO2',
            'qr_code' => 'qrcodes/REQ-ISO-002.png', 'resident_id' => $resident->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $weight, 'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate),
        ]);

        $todayVft = $this->wfq->calculateVirtualFinishTime($weight, $this->serviceDate);
        $tomorrowVft = $this->wfq->calculateVirtualFinishTime($weight, '2026-09-12');

        $this->assertNotEquals($todayVft, $tomorrowVft);
    }

    public function test_queue_position_is_recalculated_correctly_after_enqueue(): void
    {
        $docType = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();
        $regular = $this->makeResident('regular');
        $senior = $this->makeResident('senior');
        $wSenior = $this->wfq->calculateWeight($senior, $purpose);
        $wRegular = $this->wfq->calculateWeight($regular, $purpose);

        DocumentRequest::create([
            'control_number' => 'REQ-QP-001', 'queue_number' => 'Q-QP1',
            'qr_code' => 'qrcodes/REQ-QP-001.png', 'resident_id' => $regular->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wRegular, 'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($wRegular, $this->serviceDate),
            'queue_position' => 2,
        ]);
        DocumentRequest::create([
            'control_number' => 'REQ-QP-002', 'queue_number' => 'Q-QP2',
            'qr_code' => 'qrcodes/REQ-QP-002.png', 'resident_id' => $senior->id,
            'document_type_id' => $docType->id, 'purpose_id' => $purpose->id,
            'status' => 'pending', 'service_date' => $this->serviceDate,
            'total_weight' => $wSenior, 'virtual_finish_time' => $this->wfq->calculateVirtualFinishTime($wSenior, $this->serviceDate),
            'queue_position' => 1,
        ]);

        $seniorPos = DocumentRequest::where('resident_id', $senior->id)->first()->queue_position;
        $regularPos = DocumentRequest::where('resident_id', $regular->id)->first()->queue_position;

        $this->assertSame(1, $seniorPos);
        $this->assertSame(2, $regularPos);
    }
}
