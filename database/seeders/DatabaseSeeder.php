<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ResidentCategorySeeder::class,
            DocumentTypeSeeder::class,
            RequestPurposeSeeder::class,
            WFQConfigurationSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (! app()->environment('production') || filter_var(env('SEED_DEMO_DATA'), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
