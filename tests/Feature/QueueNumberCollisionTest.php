<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueueNumberCollisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    private function createExistingResident(string $email = 'existing.queue@example.com'): Resident
    {
        $user = User::create([
            'email' => $email,
            'password' => Hash::make('123456'),
            'pin' => Hash::make('123456'),
            'tracking_number' => 'EC-'.Str::upper(Str::random(6)),
            'role' => 'resident',
            'is_active' => true,
        ]);
        $user->assignRole('resident');

        return Resident::create([
            'user_id' => $user->id,
            'first_name' => 'QUEUE',
            'last_name' => 'TEST',
            'birthdate' => '1990-05-05',
            'age' => 36,
            'gender' => 'female',
            'civil_status' => 'single',
            'nationality' => 'FILIPINO',
            'street' => 'M.H. DEL PILAR ST',
            'barangay' => 'MOLINO I',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
            'category' => 'regular',
        ]);
    }

    private function seedDesyncedQueueNumbers(Resident $resident): void
    {
        // Older queue number Q-0015 seeded on a previous day, then a LOWER
        // queue number Q-0014 created today with a higher row id. The old
        // generator picked the highest id created today (Q-0014) and produced
        // Q-0015 again, colliding with the existing row.
        $older = DocumentRequest::create([
            'queue_number' => 'Q-0015',
            'resident_id' => $resident->id,
            'document_type_id' => 1,
            'purpose_id' => 1,
            'status' => 'released',
        ]);
        $older->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        DocumentRequest::create([
            'queue_number' => 'Q-0014',
            'resident_id' => $resident->id,
            'document_type_id' => 2,
            'purpose_id' => 1,
            'status' => 'pending',
        ]);
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

    private function registrationData(): array
    {
        return [
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
    }

    public function test_registration_succeeds_when_prior_queue_numbers_are_desynced(): void
    {
        $this->seedDesyncedQueueNumbers($this->createExistingResident());

        $response = $this->post(route('register'), array_merge($this->registrationData(), [
            'id_scan_front' => $this->createTestIdImage(),
            'id_scan_back' => $this->createTestIdImage(),
        ]), [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $resident = Resident::where('first_name', 'JUAN')->first();
        $this->assertNotNull($resident);

        $this->assertDatabaseHas('document_requests', [
            'resident_id' => $resident->id,
            'queue_number' => 'Q-0016',
        ]);

        $this->assertSame(
            1,
            DocumentRequest::where('queue_number', 'Q-0016')->count(),
            'The generated queue number must be unique.'
        );
    }

    public function test_logged_in_resident_request_survives_desynced_queue_numbers(): void
    {
        $resident = $this->createExistingResident();
        $this->seedDesyncedQueueNumbers($resident);

        $this->actingAs($resident->user);

        $response = $this->post(route('resident.submit-request'), [
            'document_type_id' => 3,
            'purpose_id' => 1,
            'purpose_other' => '',
        ]);

        $response->assertRedirect(route('resident.requests'));

        $this->assertDatabaseHas('document_requests', [
            'resident_id' => $resident->id,
            'document_type_id' => 3,
            'queue_number' => 'Q-0016',
        ]);
    }
}
