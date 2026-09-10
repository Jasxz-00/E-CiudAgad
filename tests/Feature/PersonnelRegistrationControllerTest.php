<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PersonnelRegistrationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    public function test_create_page_loads(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $response = $this->actingAs($personnel)->get(route('personnel.registrations.create'));

        $response->assertStatus(200);
        $response->assertSee('Register Resident');
    }

    public function test_store_creates_resident_and_audit_log(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $docType = DocumentType::first();
        $purpose = RequestPurpose::where('code', '!=', 'OTHERS')->first();

        $response = $this->actingAs($personnel)->post(route('personnel.registrations.store'), $this->validPayload($docType, $purpose));

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('residents', [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => 'SANTOS',
            'road' => 'MOLINO ROAD',
        ]);

        $this->assertDatabaseHas('personnel_registrations', [
            'personnel_id' => $personnel->id,
            'action' => 'registered',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $response = $this->actingAs($personnel)->post(route('personnel.registrations.store'), []);

        $response->assertSessionHasErrors([
            'first_name', 'last_name', 'contact_number',
            'emergency_contact', 'id_type', 'id_scan_front', 'id_scan_back', 'id_1x1',
            'document_type_id', 'purpose_id',
        ]);
    }

    public function test_store_saves_default_location(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $docType = DocumentType::first();
        $purpose = RequestPurpose::where('code', '!=', 'OTHERS')->first();

        $payload = $this->validPayload($docType, $purpose);
        $payload['first_name'] = 'MARIA';
        $payload['last_name'] = 'SANTOS';
        $payload['middle_name'] = '';
        $payload['birthdate_month'] = 3;
        $payload['birthdate_day'] = 10;
        $payload['birthdate_year'] = 1995;
        $payload['gender'] = 'female';
        $payload['contact_number'] = '0917-111-2222';
        $payload['emergency_contact'] = '0922-333-4444';
        $payload['id_type'] = 'passport';
        $payload['middle_name_none'] = 1;
        unset($payload['road']);

        $response = $this->actingAs($personnel)->post(route('personnel.registrations.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('residents', [
            'first_name' => 'MARIA',
            'last_name' => 'SANTOS',
            'road' => null,
            'city' => 'BACOOR CITY',
            'province' => 'CAVITE',
            'zip_code' => '4102',
            'middle_name_none' => 1,
            'middle_name' => null,
        ]);
    }

    public function test_store_allows_road_override(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $docType = DocumentType::first();
        $purpose = RequestPurpose::where('code', '!=', 'OTHERS')->first();

        $payload = $this->validPayload($docType, $purpose);
        $payload['first_name'] = 'PEDRO';
        $payload['last_name'] = 'GARCIA';
        $payload['middle_name'] = '';
        $payload['middle_name_none'] = 1;
        $payload['birthdate_month'] = 6;
        $payload['birthdate_day'] = 22;
        $payload['birthdate_year'] = 1988;
        $payload['contact_number'] = '0917-555-6666';
        $payload['emergency_contact'] = '0922-777-8888';
        $payload['road'] = 'AGUINALDO HIGHWAY';

        $response = $this->actingAs($personnel)->post(route('personnel.registrations.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('residents', [
            'first_name' => 'PEDRO',
            'last_name' => 'GARCIA',
            'road' => 'AGUINALDO HIGHWAY',
        ]);
    }

    public function test_show_page_displays_registered_resident(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $docType = DocumentType::first();
        $purpose = RequestPurpose::where('code', '!=', 'OTHERS')->first();

        $this->actingAs($personnel)->post(route('personnel.registrations.store'), $this->validPayload($docType, $purpose));

        $resident = Resident::where('first_name', 'JUAN')->first();
        $this->assertNotNull($resident);

        $showResponse = $this->actingAs($personnel)->get(route('personnel.registrations.show', $resident->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('JUAN');
        $showResponse->assertSee('DELA CRUZ');
    }

    protected function validPayload($docType, $purpose): array
    {
        return [
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'middle_name' => 'SANTOS',
            'birthdate_month' => 1,
            'birthdate_day' => 15,
            'birthdate_year' => 1990,
            'gender' => 'male',
            'civil_status' => 'single',
            'place_of_birth' => 'BACOOR CITY, CAVITE',
            'contact_number' => '0917-123-4567',
            'emergency_contact' => '0922-987-6543',
            'nationality' => 'FILIPINO',
            'id_type' => 'phil_id',
            'id_scan_front' => $this->createTestIdImage(),
            'id_scan_back' => $this->createTestIdImage(),
            'id_1x1' => $this->createTestIdImage(),
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'privacy_consent' => 1,
            'barangay' => 'MOLINO I',
            'street' => 'M.H. DEL PILAR ST',
            'road' => 'MOLINO ROAD',
            'middle_name_none' => 0,
        ];
    }

    protected function createTestIdImage(): UploadedFile
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
}
