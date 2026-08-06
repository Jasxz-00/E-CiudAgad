<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@eciudadagad.gov.ph'],
            [
                'username' => 'admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $personnel = User::updateOrCreate(
            ['email' => 'personnel@eciudadagad.gov.ph'],
            [
                'username' => 'personnel',
                'password' => bcrypt('personnel123'),
                'role' => 'personnel',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $personnel->assignRole('personnel');

        $resident = User::updateOrCreate(
            ['email' => 'resident@example.com'],
            [
                'username' => 'resident',
                'password' => bcrypt('resident123'),
                'role' => 'resident',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $resident->assignRole('resident');
    }
}
