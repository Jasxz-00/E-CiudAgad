<?php

namespace Tests\Feature;

use App\Models\PersonnelRegistration;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PersonnelRegistrationAuditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    public function test_personnel_registration_creates_audit_log(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');
        $resident = Resident::factory()->create();

        PersonnelRegistration::create([
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'registered',
            'metadata' => ['tracking_number' => 'TN-001', 'queue_number' => 'Q-001'],
        ]);

        $this->assertDatabaseHas('personnel_registrations', [
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'registered',
        ]);
    }

    public function test_personnel_request_filing_creates_audit_log(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');
        $resident = Resident::factory()->create();

        PersonnelRegistration::create([
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'filed_request',
            'metadata' => ['queue_number' => 'Q-002'],
        ]);

        $this->assertDatabaseHas('personnel_registrations', [
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'filed_request',
        ]);
    }

    public function test_personnel_registration_belongs_to_personnel(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');
        $resident = Resident::factory()->create();

        $log = PersonnelRegistration::create([
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'registered',
        ]);

        $this->assertTrue($log->personnel->is($personnel));
    }

    public function test_personnel_registration_belongs_to_resident(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');
        $resident = Resident::factory()->create();

        $log = PersonnelRegistration::create([
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'registered',
        ]);

        $this->assertTrue($log->resident->is($resident));
    }

    public function test_metadata_is_cast_to_array(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');
        $resident = Resident::factory()->create();

        $meta = ['tracking_number' => 'TN-003', 'queue_number' => 'Q-003'];

        $log = PersonnelRegistration::create([
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'registered',
            'metadata' => $meta,
        ]);

        $this->assertIsArray($log->metadata);
        $this->assertEquals($meta, $log->metadata);
    }

    public function test_updated_at_is_null(): void
    {
        $personnel = User::factory()->create(['role' => 'personnel']);
        $personnel->assignRole('personnel');
        $resident = Resident::factory()->create();

        $log = PersonnelRegistration::create([
            'personnel_id' => $personnel->id,
            'resident_id' => $resident->id,
            'action' => 'registered',
        ]);

        $this->assertNull($log->updated_at);
    }
}
