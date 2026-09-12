<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Resident;
use App\Models\RequestPurpose;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);

        DocumentType::firstOrCreate(
            ['code' => 'BRGY_CLEARANCE'],
            ['name' => 'Barangay Clearance', 'code' => 'BRGY_CLEARANCE', 'is_active' => true]
        );
        RequestPurpose::firstOrCreate(
            ['code' => 'POLICE_CLEARANCE'],
            ['name' => 'Police Clearance', 'code' => 'POLICE_CLEARANCE', 'is_active' => true]
        );
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_to_login_for_protected_pages(): void
    {
        $this->get(route('resident.dashboard'))->assertRedirect(route('login'));
        $this->get(route('resident.requests'))->assertRedirect(route('login'));
        $this->get(route('personnel.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('queue.monitor'))->assertRedirect(route('login'));
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_resident_cannot_access_personnel_or_admin_pages(): void
    {
        $resident = $this->makeUser('resident');

        $this->actingAs($resident)->get(route('personnel.dashboard'))->assertForbidden();
        $this->actingAs($resident)->get(route('personnel.requests'))->assertForbidden();
        $this->actingAs($resident)->get(route('queue.monitor'))->assertForbidden();
        $this->actingAs($resident)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($resident)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_personnel_cannot_access_admin_or_resident_pages(): void
    {
        $personnel = $this->makeUser('personnel');

        $this->actingAs($personnel)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($personnel)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($personnel)->get(route('admin.wfq.index'))->assertForbidden();
        $this->actingAs($personnel)->get(route('resident.dashboard'))->assertForbidden();
        $this->actingAs($personnel)->get(route('resident.profile'))->assertForbidden();
    }

    public function test_admin_cannot_access_personnel_or_resident_pages(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('personnel.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('personnel.requests'))->assertForbidden();
        $this->actingAs($admin)->get(route('resident.dashboard'))->assertForbidden();
    }

    public function test_admin_can_access_admin_pages_and_personnel_can_access_personnel_pages(): void
    {
        $admin = $this->makeUser('admin');
        $personnel = $this->makeUser('personnel');

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.wfq.index'))->assertOk();

        $this->actingAs($personnel)->get(route('personnel.dashboard'))->assertOk();
        $this->actingAs($personnel)->get(route('queue.monitor'))->assertOk();
    }

    public function test_request_show_is_restricted_to_owner_or_staff(): void
    {
        $ownerResident = $this->makeUser('resident');
        $otherResident = $this->makeUser('resident');

        Resident::create([
            'user_id' => $ownerResident->id,
            'first_name' => 'OWNER',
            'last_name' => 'ONE',
            'birthdate' => '1990-01-15',
            'age' => 35,
            'gender' => 'male',
            'nationality' => 'FILIPINO',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
        ]);

        Resident::create([
            'user_id' => $otherResident->id,
            'first_name' => 'OTHER',
            'last_name' => 'TWO',
            'birthdate' => '1991-02-20',
            'age' => 34,
            'gender' => 'female',
            'nationality' => 'FILIPINO',
            'contact_number' => '0912-111-2222',
            'emergency_contact' => '0998-333-4444',
        ]);

        $docType = DocumentType::first();
        $purpose = RequestPurpose::first();

        $request = DocumentRequest::create([
            'control_number' => 'REQ-20260910-0001',
            'queue_number' => 'A001',
            'qr_code' => 'qrcodes/REQ-20260910-0001.png',
            'resident_id' => $ownerResident->resident->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        // Owner can view
        $this->actingAs($ownerResident)->get(route('request.show', $request->control_number))->assertOk();

        // Another resident is forbidden
        $this->actingAs($otherResident)->get(route('request.show', $request->control_number))->assertForbidden();

        // Staff can view
        $personnel = $this->makeUser('personnel');
        $this->actingAs($personnel)->get(route('request.show', $request->control_number))->assertOk();
    }

    public function test_personnel_can_process_request_by_control_number(): void
    {
        $personnel = $this->makeUser('personnel');
        $ownerResident = $this->makeUser('resident');

        Resident::create([
            'user_id' => $ownerResident->id,
            'first_name' => 'OWNER',
            'last_name' => 'ONE',
            'birthdate' => '1990-01-15',
            'age' => 35,
            'gender' => 'male',
            'nationality' => 'FILIPINO',
            'contact_number' => '0912-345-6789',
            'emergency_contact' => '0998-765-4321',
        ]);

        $docType = DocumentType::first();
        $purpose = RequestPurpose::first();

        $request = DocumentRequest::create([
            'control_number' => 'REQ-20260910-0002',
            'queue_number' => 'A002',
            'qr_code' => 'qrcodes/REQ-20260910-0002.png',
            'resident_id' => $ownerResident->resident->id,
            'document_type_id' => $docType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
            'total_weight' => 0.00,
            'virtual_finish_time' => 0.000000,
        ]);

        $this->actingAs($personnel)
            ->post(route('queue.process', $request->control_number))
            ->assertRedirect();

        $this->assertDatabaseHas('document_requests', [
            'id' => $request->id,
            'control_number' => $request->control_number,
            'status' => 'reviewing',
            'processed_by' => $personnel->id,
        ]);
    }
}