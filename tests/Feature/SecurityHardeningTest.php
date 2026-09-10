<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_staff_login_locks_out_after_repeated_failures(): void
    {
        User::factory()->create([
            'role' => 'personnel',
            'username' => 'staff01',
            'password' => Hash::make('correct-password'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login_type' => 'staff',
                'login' => 'staff01',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('login');
        }

        $response = $this->post('/login', [
            'login_type' => 'staff',
            'login' => 'staff01',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_phone_otp_verification_blocks_after_repeated_failures(): void
    {
        Cache::put('phone_reset_otp_0912-345-6789', [
            'otp' => 123456,
            'user_id' => User::factory()->create(['role' => 'resident'])->id,
            'expires_at' => now()->addMinutes(10),
        ], now()->addMinutes(10));

        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password/verify-phone-otp', [
                'contact_number' => '0912-345-6789',
                'otp' => '000000',
            ])->assertSessionHasErrors('otp');
        }

        $response = $this->post('/forgot-password/verify-phone-otp', [
            'contact_number' => '0912-345-6789',
            'otp' => '123456',
        ]);

        $response->assertSessionHasErrors('otp');
    }

    public function test_legacy_credentials_route_is_removed(): void
    {
        $this->post('/register/credentials')
            ->assertNotFound();
    }
}