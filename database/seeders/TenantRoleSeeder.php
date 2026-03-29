<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantRoleSeeder extends Seeder
{
    /**
     * Seed tenant roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';
        $permissions = [
            'pos.access',
            'products.manage',
            'inventory.manage',
            'customers.manage',
            'suppliers.manage',
            'reports.view',
            'users.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        $ownerRole = Role::firstOrCreate([
            'name' => 'Owner',
            'guard_name' => $guard,
        ]);

        $administratorRole = Role::firstOrCreate([
            'name' => 'Administrator',
            'guard_name' => $guard,
        ]);

        $staffRole = Role::firstOrCreate([
            'name' => 'Staff',
            'guard_name' => $guard,
        ]);

        $cashierRole = Role::firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => $guard,
        ]);

        $ownerRole->syncPermissions($permissions);
        $administratorRole->syncPermissions($permissions);
        $staffRole->syncPermissions(['pos.access']);
        $cashierRole->syncPermissions(['pos.access']);
    }
}
