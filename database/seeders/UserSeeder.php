<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Services\EmailService;
use App\Helpers\EmailHelper;

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
            $ownerUser = User::create([
                'tenant_id' => $tenant->tenant_id,
                'username' => 'owner',
                'name' => 'John Owner',
                'email' => EmailHelper::getBusinessEmail('owner', $tenant->business_name),
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
            
            $ownerUser->syncRoles(['Owner']);

            // Create manager user for premium tenant
            if ($tenant->subscription['plan'] === 'premium') {
                $managerUser = User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'manager',
                    'name' => 'Jane Manager',
                    'email' => EmailHelper::getBusinessEmail('manager', $tenant->business_name),
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
                
                $managerUser->syncRoles(['Manager']);

                // Create cashier user
                $cashierUser = User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'cashier',
                    'name' => 'Mike Cashier',
                    'email' => EmailHelper::getBusinessEmail('cashier', $tenant->business_name),
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
                
                $cashierUser->syncRoles(['Cashier']);

                // Create inventory staff user
                $inventoryUser = User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'inventory',
                    'name' => 'Sarah Inventory',
                    'email' => EmailHelper::getBusinessEmail('inventory', $tenant->business_name),
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
                
                $inventoryUser->syncRoles(['Inventory Staff']);
            }

            // Create additional users for standard tenant
            if ($tenant->subscription['plan'] === 'standard') {
                $standardManagerUser = User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'manager',
                    'name' => 'Bob Manager',
                    'email' => EmailHelper::getBusinessEmail('manager', $tenant->business_name),
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
                
                $standardManagerUser->syncRoles(['Manager']);

                $standardCashierUser = User::create([
                    'tenant_id' => $tenant->tenant_id,
                    'username' => 'cashier',
                    'name' => 'Lisa Cashier',
                    'email' => EmailHelper::getBusinessEmail('cashier', $tenant->business_name),
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
                
                $standardCashierUser->syncRoles(['Cashier']);
            }
        }

        // Send welcome emails to all created users
        foreach ($tenants as $tenant) {
            $tenantUsers = User::where('tenant_id', $tenant->tenant_id)->get();
            
            foreach ($tenantUsers as $user) {
                $password = 'password123'; // Default seeder password
                
                $emailResult = EmailService::sendWelcomeEmail(
                    $user->email,
                    $tenant->business_name,
                    $user->role,
                    $password
                );

                // Log email result for debugging
                if (!$emailResult['success']) {
                    \Log::error("Failed to send welcome email to {$user->email}: " . $emailResult['error']);
                }
            }
        }

        // Update tenant usage for users
        foreach ($tenants as $tenant) {
            $userCount = User::where('tenant_id', $tenant->tenant_id)->count();
            $tenant->updateUsage('users', $userCount);
        }
    }
}
