<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResidentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'regular', 'display_name' => 'Regular', 'description' => 'General resident category', 'weight' => 1.00],
            ['name' => 'pwd', 'display_name' => 'Person with Disability', 'description' => 'Residents with valid disability proof', 'weight' => 4.00],
            ['name' => 'pregnant', 'display_name' => 'Pregnant', 'description' => 'Pregnant residents (female only)', 'weight' => 3.00],
            ['name' => 'senior', 'display_name' => 'Senior Citizen', 'description' => 'Residents aged 60 years and above', 'weight' => 2.00],
        ];

        foreach ($categories as $cat) {
            DB::table('resident_categories')->updateOrInsert(
                ['name' => $cat['name']],
                array_merge($cat, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
