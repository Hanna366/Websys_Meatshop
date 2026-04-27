<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantRolesSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'manage_users',
            'manage_products',
            'process_sales',
            'view_reports',
            'manage_settings',
        ];

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Run inside tenant context so Spatie tables are created in tenant DB
            $tenant->run(function () use ($permissions) {
                foreach ($permissions as $perm) {
                    Permission::firstOrCreate(['name' => $perm]);
                }

                $admin = Role::firstOrCreate(['name' => 'admin']);
                $admin->syncPermissions($permissions);

                $staff = Role::firstOrCreate(['name' => 'staff']);
                $staff->syncPermissions(['manage_products', 'process_sales', 'view_reports']);

                $user = Role::firstOrCreate(['name' => 'user']);
                $user->syncPermissions(['process_sales']);
            });
        }
    }
}
