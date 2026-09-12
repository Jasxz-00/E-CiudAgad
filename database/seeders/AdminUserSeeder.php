<?php

namespace Database\Seeders;

/**
 * ⚠️ DEMONSTRATION DATA ONLY ⚠️
 * 
 * Admin and personnel credentials are for local development/testing only.
 * Production credentials MUST come from protected environment variables
 * or a secure first-user setup. Never deploy with these default credentials.
 */

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@eciudadagad.gov.ph'],
            [
                'username' => 'admin-eciudadagad',
                'password' => bcrypt('Str0ngP@ss2026!'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $personnel = User::updateOrCreate(
            ['email' => 'personnel@eciudadagad.gov.ph'],
            [
                'username' => 'personnel-eciudadagad',
                'password' => bcrypt('Secur3P@ss2026!'),
                'role' => 'personnel',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $personnel->assignRole('personnel');

        $resident = User::updateOrCreate(
            ['email' => 'resident@eciudadagad.gov.ph'],
            [
                'username' => 'resident-eciudadagad',
                'password' => bcrypt('R3s1d3ntP@ss2026!'),
                'role' => 'resident',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $resident->assignRole('resident');
    }
}
