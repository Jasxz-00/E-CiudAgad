<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileFieldAllowlistTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    public function test_readonly_fields_are_not_updated(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'role' => 'resident',
        ]);
        $user->assignRole('resident');

        $resident = Resident::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'birthdate' => '1990-01-15',
            'age' => 35,
            'gender' => 'male',
            'nationality' => 'FILIPINO',
            'category' => 'regular',
        ]);

        $response = $this->actingAs($user)
            ->put(route('resident.profile.update'), [
                'contact_number' => '0917-123-4567',
                'emergency_contact' => '0922-987-6543',
                'email' => 'updated@example.com',
                'civil_status' => 'married',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $resident->refresh();
        $this->assertEquals('JUAN', $resident->first_name);
        $this->assertEquals('DELA CRUZ', $resident->last_name);
        $this->assertEquals('male', $resident->gender);
        $this->assertEquals('FILIPINO', $resident->nationality);
    }

    public function test_editable_fields_are_updated(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'role' => 'resident',
        ]);
        $user->assignRole('resident');

        $resident = Resident::factory()->create([
            'user_id' => $user->id,
            'contact_number' => '0917-111-1111',
            'emergency_contact' => '0922-111-1111',
            'civil_status' => 'single',
            'street' => 'OLD STREET',
            'barangay' => 'OLD BARANGAY',
            'religion' => null,
            'place_of_birth' => null,
        ]);

        $response = $this->actingAs($user)
            ->put(route('resident.profile.update'), [
                'contact_number' => '0917-222-2222',
                'emergency_contact' => '0922-222-2222',
                'email' => 'updated@example.com',
                'civil_status' => 'married',
                'street' => 'NEW STREET',
                'barangay' => 'NEW BARANGAY',
                'purok' => 'PUROK 5',
                'religion' => 'ROMAN CATHOLIC',
                'place_of_birth' => 'BACOOR CITY, CAVITE',
                'occupation' => 'ENGINEER',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $resident->refresh();
        $this->assertEquals('0917-222-2222', $resident->contact_number);
        $this->assertEquals('0922-222-2222', $resident->emergency_contact);
        $this->assertEquals('married', $resident->civil_status);
        $this->assertEquals('NEW STREET', $resident->street);
        $this->assertEquals('NEW BARANGAY', $resident->barangay);
        $this->assertEquals('PUROK 5', $resident->purok);
        $this->assertStringContainsStringIgnoringCase('ROMAN CATHOLIC', $resident->religion ?? '');
        $this->assertEquals('BACOOR CITY, CAVITE', $resident->place_of_birth);
        $this->assertEquals('ENGINEER', $resident->occupation);
    }

    public function test_non_editable_fields_have_no_input_in_view(): void
    {
        $user = User::factory()->create(['role' => 'resident']);
        $user->assignRole('resident');
        $resident = Resident::factory()->create([
            'user_id' => $user->id,
            'birthdate' => '1990-06-15',
        ]);

        $response = $this->actingAs($user)
            ->get(route('resident.profile'));

        $response->assertStatus(200);

        $response->assertDontSee('name="first_name"', false);
        $response->assertDontSee('name="last_name"', false);
        $response->assertDontSee('name="gender"', false);
        $response->assertDontSee('name="nationality"', false);
    }
}
