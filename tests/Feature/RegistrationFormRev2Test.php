<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RegistrationFormRev2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
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

    public function test_default_location_is_bacoor_city_cavite(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('name="city"', false);
        $response->assertSee('name="province"', false);
        $response->assertSee('name="zip_code"', false);
        $response->assertSee('BACOOR CITY, CAVITE 4102');
    }

    public function test_road_field_is_present(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('name="road"', false);
    }

    public function test_middle_name_none_checkbox_exists(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('middleNameNone', false);
        $response->assertSee('name="middle_name_none"', false);
    }

    public function test_philhealth_in_id_options(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('PhilHealth');
        $response->assertSee('philhealth', false);
    }

    public function test_validate_step_function_exists(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('validateStep', false);
    }

    public function test_asterisk_on_required_fields(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('<span class="text-danger">*</span>', false);
    }

    public function test_person_status_has_required_asterisk(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('x-text="t(\'person_status\')"', false);
        $response->assertSee('<span class="text-danger">*</span>', false);
    }

    public function test_person_status_photo_upload_present(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('status_verification_photo', false);
    }

    public function test_landing_page_defaults_to_filipino(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('landing_lang', false);
        $response->assertSee("'fil'", false);
    }

    public function test_theme_toggle_on_register_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('theme-toggle', false);
    }

    public function test_register_returns_422_json_on_validation_failure_when_expecting_json(): void
    {
        $response = $this->post(route('register'), [], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['first_name', 'last_name', 'contact_number', 'emergency_contact', 'id_type', 'id_scan_front', 'id_scan_back', 'document_type_id']]);
    }

    public function test_valid_registration_creates_account_and_returns_json(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);

        $idFile = $this->createTestIdImage();

        $data = [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => 'SANTOS',
            'suffix' => '',
            'nationality' => 'FILIPINO',
            'occupation' => 'TEACHER',
            'birthdate_month' => '1',
            'birthdate_day' => '15',
            'birthdate_year' => '1990',
            'gender' => 'male',
            'civil_status' => 'single',
            'religion' => 'ROMAN CATHOLIC',
            'place_of_birth' => 'BACOOR CITY, CAVITE',
            'person_status' => '',
            'building_no' => '10',
            'unit_no' => '',
            'street' => 'M.H. DEL PILAR ST',
            'road' => 'MOLINO ROAD',
            'barangay' => 'MOLINO I',
            'subdivision' => 'PHASE 1A',
            'purok' => '',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
            'email' => '',
            'id_type' => 'phil_id',
            'document_type_id' => '1',
            'purpose_id' => '1',
            'privacy_consent' => '1',
            'assisted_mode' => '0',
            'is_pregnant' => '0',
        ];

        $response = $this->post(route('register'), array_merge($data, [
            'id_scan_front' => $idFile,
            'id_scan_back' => $idFile,
            'id_1x1' => $idFile,
        ]), [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'redirect']);
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('users', [
            'email' => 'resident_'.$response->json('credentials.tracking_number').'@example.com',
            'role' => 'resident',
        ]);

        $this->assertDatabaseHas('residents', [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'gender' => 'male',
        ]);

        $this->assertDatabaseHas('document_requests', [
            'document_type_id' => 1,
            'purpose_id' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_registration_fails_when_id_front_and_back_are_missing(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);

        $data = [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => 'SANTOS',
            'suffix' => '',
            'nationality' => 'FILIPINO',
            'occupation' => 'TEACHER',
            'birthdate_month' => '1',
            'birthdate_day' => '15',
            'birthdate_year' => '1990',
            'gender' => 'male',
            'civil_status' => 'single',
            'religion' => 'ROMAN CATHOLIC',
            'place_of_birth' => 'BACOOR CITY, CAVITE',
            'person_status' => '',
            'building_no' => '10',
            'unit_no' => '',
            'street' => 'M.H. DEL PILAR ST',
            'road' => 'MOLINO ROAD',
            'barangay' => 'MOLINO I',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
            'email' => '',
            'id_type' => 'phil_id',
            'document_type_id' => '1',
            'purpose_id' => '1',
            'privacy_consent' => '1',
            'assisted_mode' => '0',
            'is_pregnant' => '0',
        ];

        $response = $this->post(route('register'), $data, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['id_scan_front', 'id_scan_back']]);
    }

    public function test_middle_name_none_checkbox_value_1_passes_validation(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);

        $idFile = $this->createTestIdImage();

        $data = [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => '',
            'middle_name_none' => '1',
            'suffix' => '',
            'nationality' => 'FILIPINO',
            'occupation' => 'TEACHER',
            'birthdate_month' => '1',
            'birthdate_day' => '15',
            'birthdate_year' => '1990',
            'gender' => 'male',
            'civil_status' => 'single',
            'religion' => 'ROMAN CATHOLIC',
            'place_of_birth' => 'BACOOR CITY, CAVITE',
            'person_status' => '',
            'building_no' => '10',
            'unit_no' => '',
            'street' => 'M.H. DEL PILAR ST',
            'road' => 'MOLINO ROAD',
            'barangay' => 'MOLINO I',
            'subdivision' => 'PHASE 1A',
            'purok' => '',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
            'email' => '',
            'id_type' => 'phil_id',
            'document_type_id' => '1',
            'purpose_id' => '1',
            'privacy_consent' => '1',
            'assisted_mode' => '0',
            'is_pregnant' => '0',
        ];

        $response = $this->post(route('register'), array_merge($data, [
            'id_scan_front' => $idFile,
            'id_scan_back' => $idFile,
            'id_1x1' => $idFile,
        ]), [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'redirect']);
        $this->assertTrue($response->json('success'));
    }

    public function test_invalid_calendar_birthdate_is_rejected(): void
    {
        $idFile = $this->createTestIdImage();

        $data = [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => 'SANTOS',
            'middle_name_none' => '0',
            'suffix' => '',
            'nationality' => 'FILIPINO',
            'birthdate_month' => '2',
            'birthdate_day' => '31',
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
            'document_type_id' => '1',
            'purpose_id' => '1',
            'privacy_consent' => '1',
            'assisted_mode' => '0',
            'is_pregnant' => '0',
            'id_scan_front' => $idFile,
            'id_scan_back' => $idFile,
            'id_1x1' => $idFile,
        ];

        $response = $this->post(route('register'), $data, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['birthdate_day']]);
    }

    public function test_registration_without_middle_name_requires_none_checkbox(): void
    {
        $idFile = $this->createTestIdImage();

        $data = [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => '',
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
            'document_type_id' => '1',
            'purpose_id' => '1',
            'privacy_consent' => '1',
            'assisted_mode' => '0',
            'is_pregnant' => '0',
            'id_scan_front' => $idFile,
            'id_scan_back' => $idFile,
            'id_1x1' => $idFile,
        ];

        $response = $this->post(route('register'), $data, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['middle_name']]);
    }

    public function test_pregnant_category_only_allowed_for_female(): void
    {
        $idFile = $this->createTestIdImage();

        $data = [
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
            'document_type_id' => '1',
            'purpose_id' => '1',
            'privacy_consent' => '1',
            'assisted_mode' => '0',
            'is_pregnant' => '1',
            'id_scan_front' => $idFile,
            'id_scan_back' => $idFile,
            'id_1x1' => $idFile,
        ];

        $response = $this->post(route('register'), $data, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['is_pregnant']]);
    }
}
