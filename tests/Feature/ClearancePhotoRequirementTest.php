<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearancePhotoRequirementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    public function test_resident_can_submit_request_with_valid_data(): void
    {
        $resident = Resident::factory()->create(['user_id' => User::factory()->create(['role' => 'resident'])->id]);
        $resident->user->assignRole('resident');
        $clearance = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $response = $this->actingAs($resident->user)
            ->post(route('resident.submit-request'), [
                'document_type_id' => $clearance->id,
                'purpose_id' => $purpose->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('document_requests', [
            'resident_id' => $resident->id,
            'document_type_id' => $clearance->id,
            'status' => 'pending',
        ]);
    }

    public function test_resident_cannot_submit_without_document_type(): void
    {
        $resident = Resident::factory()->create(['user_id' => User::factory()->create(['role' => 'resident'])->id]);
        $resident->user->assignRole('resident');
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $response = $this->actingAs($resident->user)
            ->post(route('resident.submit-request'), [
                'purpose_id' => $purpose->id,
            ]);

        $response->assertSessionHasErrors('document_type_id');
        $this->assertDatabaseMissing('document_requests', ['resident_id' => $resident->id]);
    }

    public function test_personnel_cannot_file_request_for_nonexistent_resident(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');

        $clearance = DocumentType::where('code', 'BRGY_CLEARANCE')->first();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->first();

        $response = $this->actingAs($personnel)
            ->post(route('personnel.resident-requests.store'), [
                'resident_id' => 99999,
                'document_type_id' => $clearance->id,
                'purpose_id' => $purpose->id,
            ]);

        $response->assertSessionHasErrors('resident_id');
        $this->assertDatabaseMissing('document_requests', ['resident_id' => 99999]);
    }
}