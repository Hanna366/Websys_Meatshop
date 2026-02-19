<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Create owner user for each tenant
            User::create([
                'tenant_id' => $tenant->tenant_id,
                'username' => 'owner@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                'email' => 'owner@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                'password' => bcrypt('password123'),
                'role' => 'owner',
                'profile' => [
                    'first_name' => 'John',
                    'last_name' => 'Owner',
                    'phone' => '+1-555-0001',
                    'address' => $tenant->business_address
                ],
                'permissions' => [
                    'can_manage_users' => true,
                    'can_manage_inventory' => true,
                    'can_process_sales' => true,
                    'can_view_reports' => true,
                    'can_manage_suppliers' => true,
                    'can_manage_customers' => true,
                    'can_export_data' => true,
                    'can_access_api' => true
                ],
                'preferences' => [
                    'language' => 'en',
                    'timezone' => 'America/New_York',
                    'theme' => 'light',
                    'email_notifications' => true,
                    'sms_notifications' => false
                ],
                'status' => 'active'
            ]);

            // Create manager user for premium tenant
            if ($tenant->subscription['plan'] === 'premium') {
                User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'manager@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'email' => 'manager@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'password' => bcrypt('password123'),
                    'role' => 'manager',
                    'profile' => [
                        'first_name' => 'Jane',
                        'last_name' => 'Manager',
                        'phone' => '+1-555-0002',
                        'address' => $tenant->business_address
                    ],
                    'permissions' => [
                        'can_manage_users' => false,
                        'can_manage_inventory' => true,
                        'can_process_sales' => true,
                        'can_view_reports' => true,
                        'can_manage_suppliers' => true,
                        'can_manage_customers' => true,
                        'can_export_data' => true,
                        'can_access_api' => false
                    ],
                    'preferences' => [
                        'language' => 'en',
                        'timezone' => 'America/New_York',
                        'theme' => 'light',
                        'email_notifications' => true,
                        'sms_notifications' => true
                    ],
                    'status' => 'active'
                ]);

                // Create cashier user
                User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'cashier@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'email' => 'cashier@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'password' => bcrypt('password123'),
                    'role' => 'cashier',
                    'profile' => [
                        'first_name' => 'Mike',
                        'last_name' => 'Cashier',
                        'phone' => '+1-555-0003',
                        'address' => $tenant->business_address
                    ],
                    'permissions' => [
                        'can_manage_users' => false,
                        'can_manage_inventory' => false,
                        'can_process_sales' => true,
                        'can_view_reports' => false,
                        'can_manage_suppliers' => false,
                        'can_manage_customers' => true,
                        'can_export_data' => false,
                        'can_access_api' => false
                    ],
                    'preferences' => [
                        'language' => 'en',
                        'timezone' => 'America/New_York',
                        'theme' => 'light',
                        'email_notifications' => false,
                        'sms_notifications' => false
                    ],
                    'status' => 'active'
                ]);

                // Create inventory staff user
                User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'inventory@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'email' => 'inventory@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'password' => bcrypt('password123'),
                    'role' => 'inventory_staff',
                    'profile' => [
                        'first_name' => 'Sarah',
                        'last_name' => 'Inventory',
                        'phone' => '+1-555-0004',
                        'address' => $tenant->business_address
                    ],
                    'permissions' => [
                        'can_manage_users' => false,
                        'can_manage_inventory' => true,
                        'can_process_sales' => false,
                        'can_view_reports' => false,
                        'can_manage_suppliers' => false,
                        'can_manage_customers' => false,
                        'can_export_data' => false,
                        'can_access_api' => false
                    ],
                    'preferences' => [
                        'language' => 'en',
                        'timezone' => 'America/New_York',
                        'theme' => 'light',
                        'email_notifications' => true,
                        'sms_notifications' => false
                    ],
                    'status' => 'active'
                ]);
            }

            // Create additional users for standard tenant
            if ($tenant->subscription['plan'] === 'standard') {
                User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'manager@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'email' => 'manager@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'password' => bcrypt('password123'),
                    'role' => 'manager',
                    'profile' => [
                        'first_name' => 'Bob',
                        'last_name' => 'Manager',
                        'phone' => '+1-555-0101',
                        'address' => $tenant->business_address
                    ],
                    'permissions' => [
                        'can_manage_users' => false,
                        'can_manage_inventory' => true,
                        'can_process_sales' => true,
                        'can_view_reports' => true,
                        'can_manage_suppliers' => true,
                        'can_manage_customers' => true,
                        'can_export_data' => true,
                        'can_access_api' => false
                    ],
                    'preferences' => [
                        'language' => 'en',
                        'timezone' => 'America/New_York',
                        'theme' => 'light',
                        'email_notifications' => true,
                        'sms_notifications' => false
                    ],
                    'status' => 'active'
                ]);

                User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'cashier@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'email' => 'cashier@' . strtolower(str_replace(' ', '', $tenant->business_name)) . '.com',
                    'password' => bcrypt('password123'),
                    'role' => 'cashier',
                    'profile' => [
                        'first_name' => 'Lisa',
                        'last_name' => 'Cashier',
                        'phone' => '+1-555-0102',
                        'address' => $tenant->business_address
                    ],
                    'permissions' => [
                        'can_manage_users' => false,
                        'can_manage_inventory' => false,
                        'can_process_sales' => true,
                        'can_view_reports' => false,
                        'can_manage_suppliers' => false,
                        'can_manage_customers' => true,
                        'can_export_data' => false,
                        'can_access_api' => false
                    ],
                    'preferences' => [
                        'language' => 'en',
                        'timezone' => 'America/New_York',
                        'theme' => 'light',
                        'email_notifications' => false,
                        'sms_notifications' => false
                    ],
                    'status' => 'active'
                ]);
            }
        }

        // Update tenant usage for users
        foreach ($tenants as $tenant) {
            $userCount = User::where('tenant_id', $tenant->tenant_id)->count();
            $tenant->updateUsage('users', $userCount);
        }
    }
}
