<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'manage requests',
            'verify documents',
            'process requests',
            'manage users',
            'manage wfq',
            'manage master data',
            'manage announcements',
            'view reports',
            'manage system',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $resident = Role::findOrCreate('resident');
        $resident->givePermissionTo(['view dashboard']);

        $personnel = Role::findOrCreate('personnel');
        $personnel->givePermissionTo([
            'view dashboard',
            'manage requests',
            'verify documents',
            'process requests',
        ]);

        $admin = Role::findOrCreate('admin');
        $admin->givePermissionTo(Permission::all());
    }
}
