<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $modules = [
            'sales' => ['view', 'create', 'refund'],
            'inventory' => ['view', 'add_stock', 'update_stock', 'adjust_stock'],
            'products' => ['view', 'create', 'update', 'delete'],
            'users' => ['view', 'create', 'assign_roles'],
            'settings' => ['manage'],
        ];

        $allPermissions = [];

        foreach ($modules as $module => $perms) {
            foreach ($perms as $perm) {
                $name = "$module.$perm";
                // Create permission on tenant connection
                Permission::on('tenant')->firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
                $allPermissions[] = $name;
            }
        }

        // Create Administrator role with all permissions
        $adminRole = Role::on('tenant')->firstOrCreate([
            'name' => 'Administrator',
            'guard_name' => $guard,
        ]);
        $adminRole->syncPermissions($allPermissions);

        // Create Cashier role with limited permissions
        $cashierPerms = [
            'sales.view',
            'sales.create',
            'inventory.view',
            'products.view',
        ];

        $cashierRole = Role::on('tenant')->firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => $guard,
        ]);
        $cashierRole->syncPermissions($cashierPerms);
    }
}
