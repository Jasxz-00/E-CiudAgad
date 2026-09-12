<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\RequestPurpose;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DocumentTypePurposeMappingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypePurposeSeeder']);
    }

    private function doc(string $code): DocumentType
    {
        return DocumentType::where('code', $code)->firstOrFail();
    }

    private function purpose(string $code): RequestPurpose
    {
        return RequestPurpose::where('code', $code)->firstOrFail();
    }

    private function mappedPurposeCodes(string $docCode): array
    {
        return $this->doc($docCode)->purposes()
            ->pluck('request_purposes.code')
            ->sort()
            ->values()
            ->all();
    }

    private function registrationData(int $documentTypeId, int $purposeId): array
    {
        return [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => 'SANTOS',
            'middle_name_none' => '0',
            'suffix' => '',
            'nationality' => 'FILIPINO',
            'birthdate_month' => '1',
            'birthdate_day' => '15',
            'birthdate_year' => '1990',
            'gender' => 'male',
            'civil_status' => 'single',
            'place_of_birth' => 'BACOOR CITY, CAVITE',
            'person_status' => '',
            'street' => 'M.H. DEL PILAR ST',
            'barangay' => 'MOLINO I',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
            'id_type' => 'phil_id',
            'proof_type' => 'valid_id',
            'document_type_id' => (string) $documentTypeId,
            'purpose_id' => (string) $purposeId,
            'privacy_consent' => '1',
            'assisted_mode' => '0',
        ];
    }

    private function createTestIdImage(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_id_').'.png';
        $img = imagecreatetruecolor(800, 600);
        $bg = imagecolorallocate($img, 240, 240, 240);
        imagefill($img, 0, 0, $bg);
        for ($i = 0; $i < 5000; $i++) {
            $x = rand(0, 799);
            $y = rand(0, 599);
            $c = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($img, $x, $y, $c);
        }
        $black = imagecolorallocate($img, 0, 0, 0);
        imagestring($img, 5, 100, 100, 'NAME JUAN DELA CRUZ', $black);
        imagestring($img, 5, 100, 140, 'M.H. DEL PILAR ST MOLINO I', $black);
        imagestring($img, 5, 100, 180, '1234-5678-9012-3456', $black);
        imagepng($img, $tempPath);
        imagedestroy($img);

        return new UploadedFile($tempPath, 'id.png', 'image/png', null, true);
    }

    private function submit(array $data)
    {
        return $this->post(route('register'), $data, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
    }

    public function test_certificate_of_residency_uses_its_mapped_purposes(): void
    {
        $this->assertEquals([
            'EDUCATION_SCHOOL_REQUIREMENT',
            'FINANCIAL_ASSISTANCE',
            'FINANCIAL_LOAN_TRANSACTION',
            'LEGAL_REQUIREMENT',
            'MEDICAL_ASSISTANCE',
            'PROOF_OF_ADDRESS',
            'SCHOLARSHIP',
        ], $this->mappedPurposeCodes('CERT_RESIDENCY'));
    }

    public function test_certificate_of_indigency_uses_its_mapped_purposes(): void
    {
        $this->assertEquals([
            'EDUCATION_SCHOOL_REQUIREMENT',
            'FINANCIAL_ASSISTANCE',
            'FINANCIAL_LOAN_TRANSACTION',
            'LEGAL_REQUIREMENT',
            'MEDICAL_ASSISTANCE',
            'SCHOLARSHIP',
        ], $this->mappedPurposeCodes('CERT_INDIGENCY'));
    }

    public function test_barangay_clearance_uses_its_mapped_purposes(): void
    {
        $this->assertEquals([
            'EDUCATION_SCHOOL_REQUIREMENT',
            'FINANCIAL_ASSISTANCE',
            'FINANCIAL_LOAN_TRANSACTION',
            'LEGAL_REQUIREMENT',
            'MEDICAL_ASSISTANCE',
            'SCHOLARSHIP',
        ], $this->mappedPurposeCodes('BRGY_CLEARANCE'));
    }

    public function test_certificate_of_good_moral_uses_its_mapped_purposes(): void
    {
        $this->assertEquals([
            'EDUCATION',
            'EMPLOYMENT',
        ], $this->mappedPurposeCodes('CERT_GOOD_MORAL'));
    }

    public function test_register_page_renders_mapped_purpose_keys(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        foreach (['purpose_education_school_requirement', 'purpose_financial_loan_transaction', 'purpose_legal_requirement', 'purpose_proof_of_address', 'purpose_employment', 'purpose_education'] as $key) {
            $response->assertSee($key, false);
        }
    }

    public function test_valid_document_purpose_combo_is_accepted(): void
    {
        $residency = $this->doc('CERT_RESIDENCY');
        $legal = $this->purpose('LEGAL_REQUIREMENT');

        $response = $this->submit(array_merge($this->registrationData($residency->id, $legal->id), [
            'id_scan_front' => $this->createTestIdImage(),
            'id_scan_back' => $this->createTestIdImage(),
            'id_1x1' => $this->createTestIdImage(),
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_purpose_not_available_for_document_is_rejected(): void
    {
        $goodMoral = $this->doc('CERT_GOOD_MORAL');
        $legal = $this->purpose('LEGAL_REQUIREMENT');

        $response = $this->submit($this->registrationData($goodMoral->id, $legal->id));

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['purpose_id']]);
    }

    public function test_purpose_combo_is_enforced_when_assisted_mode_is_disabled(): void
    {
        $goodMoral = $this->doc('CERT_GOOD_MORAL');
        $legal = $this->purpose('LEGAL_REQUIREMENT');

        $response = $this->submit(array_merge($this->registrationData($goodMoral->id, $legal->id), [
            'assisted_mode' => '0',
        ]));

        $response->assertStatus(422);
    }

    public function test_purpose_combo_is_enforced_in_assisted_mode(): void
    {
        $personnel = \App\Models\User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $goodMoral = $this->doc('CERT_GOOD_MORAL');
        $medical = $this->purpose('MEDICAL_ASSISTANCE');

        $data = array_merge($this->registrationData($goodMoral->id, $medical->id), [
            'assisted_mode' => '1',
            'staff_badge' => 'P-001',
            'id_scan_front' => $this->createTestIdImage(),
            'id_scan_back' => $this->createTestIdImage(),
            'id_1x1' => $this->createTestIdImage(),
        ]);

        $response = $this->actingAs($personnel)->post(route('personnel.registrations.store'), $data);

        $response->assertSessionHasErrors(['purpose_id']);
        $this->assertDatabaseMissing('document_requests', [
            'document_type_id' => $goodMoral->id,
            'purpose_id' => $medical->id,
        ]);
    }
}