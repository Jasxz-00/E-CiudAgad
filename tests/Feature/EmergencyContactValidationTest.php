<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmergencyContactValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
    }

    public function test_emergency_contact_must_not_equal_contact_number(): void
    {
        $data = [
            'contact_number' => '0917-123-4567',
            'emergency_contact' => '0917-123-4567',
        ];

        request()->merge($data);

        $rules = [
            'contact_number' => ['required', 'string', 'regex:/^09\d{2}-\d{3}-\d{4}$/'],
            'emergency_contact' => [
                'required',
                'string',
                'regex:/^09\d{2}-\d{3}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    if ($value === request('contact_number')) {
                        $fail('Emergency contact must not be the same as your contact number.');
                    }
                },
            ],
        ];

        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('emergency_contact', $validator->errors()->toArray());
    }

    public function test_emergency_contact_allowed_when_different(): void
    {
        $data = [
            'contact_number' => '0917-123-4567',
            'emergency_contact' => '0922-987-6543',
        ];

        request()->merge($data);

        $rules = [
            'contact_number' => ['required', 'string'],
            'emergency_contact' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value === request('contact_number')) {
                        $fail('Emergency contact must not be the same.');
                    }
                },
            ],
        ];

        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_emergency_contact_must_match_phone_format(): void
    {
        $rules = [
            'emergency_contact' => ['required', 'string', 'regex:/^09\d{2}-\d{3}-\d{4}$/'],
        ];

        $validator = Validator::make(['emergency_contact' => '12345'], $rules);
        $this->assertTrue($validator->fails());
    }

    public function test_profile_update_rejects_same_emergency_contact(): void
    {
        $user = User::factory()->create(['role' => 'resident']);
        $user->assignRole('resident');
        $resident = Resident::factory()->create([
            'user_id' => $user->id,
            'contact_number' => '0917-123-4567',
            'emergency_contact' => '0922-987-6543',
        ]);

        $response = $this->actingAs($user)
            ->put(route('resident.profile.update'), [
                'contact_number' => '0917-111-1111',
                'emergency_contact' => '0917-111-1111',
            ]);

        $response->assertSessionHasErrors('emergency_contact');
    }

    public function test_profile_update_succeeds_with_different_contacts(): void
    {
        $user = User::factory()->create(['role' => 'resident']);
        $user->assignRole('resident');
        $resident = Resident::factory()->create([
            'user_id' => $user->id,
            'contact_number' => '0917-123-4567',
            'emergency_contact' => '0922-987-6543',
        ]);

        $response = $this->actingAs($user)
            ->put(route('resident.profile.update'), [
                'contact_number' => '0917-111-1111',
                'emergency_contact' => '0922-222-2222',
                'email' => $user->email,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
    }
}
