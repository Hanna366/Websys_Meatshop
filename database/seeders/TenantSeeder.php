<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Tenant::create([
            'tenant_id' => 'TEN' . strtoupper(\Illuminate\Support\Str::random(8)),
            'business_name' => 'Premium Meats Inc.',
            'business_email' => 'info@premiummeats.com',
            'business_phone' => '+1-555-0123',
            'business_address' => [
                'street' => '123 Main St',
                'city' => 'Meatville',
                'state' => 'TX',
                'zip_code' => '75001',
                'country' => 'US'
            ],
            'subscription' => [
                'plan' => 'premium',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'monthly_price' => 149,
                'features' => [
                    ['name' => 'inventory_tracking', 'enabled' => true],
                    ['name' => 'pos_system', 'enabled' => true],
                    ['name' => 'supplier_management', 'enabled' => true],
                    ['name' => 'customer_management', 'enabled' => true],
                    ['name' => 'advanced_reporting', 'enabled' => true],
                    ['name' => 'api_access', 'enabled' => true],
                    ['name' => 'batch_operations', 'enabled' => true],
                    ['name' => 'data_export', 'enabled' => true]
                ]
            ],
            'settings' => [
                'currency' => 'USD',
                'weight_unit' => 'lb',
                'tax_rate' => 8.25,
                'low_stock_threshold' => 10,
                'expiry_warning_days' => 7,
                'enable_sms_notifications' => true,
                'enable_email_notifications' => true
            ],
            'usage' => [
                'users_count' => 0,
                'products_count' => 0,
                'storage_used' => 0,
                'api_calls_this_month' => 0
            ],
            'limits' => [
                'max_users' => -1, // unlimited
                'max_products' => -1,
                'max_storage_mb' => 20000,
                'max_api_calls_per_month' => 50000
            ],
            'status' => 'active'
        ]);

        Tenant::create([
            'tenant_id' => 'TEN' . strtoupper(\Illuminate\Support\Str::random(8)),
            'business_name' => 'Local Butcher Shop',
            'business_email' => 'contact@localbutcher.com',
            'business_phone' => '+1-555-0456',
            'business_address' => [
                'street' => '456 Oak Ave',
                'city' => 'Butchertown',
                'state' => 'CA',
                'zip_code' => '90210',
                'country' => 'US'
            ],
            'subscription' => [
                'plan' => 'standard',
                'status' => 'trial',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'monthly_price' => 79,
                'features' => [
                    ['name' => 'inventory_tracking', 'enabled' => true],
                    ['name' => 'pos_system', 'enabled' => true],
                    ['name' => 'supplier_management', 'enabled' => true],
                    ['name' => 'customer_management', 'enabled' => true],
                    ['name' => 'basic_reporting', 'enabled' => true]
                ]
            ],
            'settings' => [
                'currency' => 'USD',
                'weight_unit' => 'lb',
                'tax_rate' => 7.5,
                'low_stock_threshold' => 5,
                'expiry_warning_days' => 5,
                'enable_sms_notifications' => false,
                'enable_email_notifications' => true
            ],
            'usage' => [
                'users_count' => 0,
                'products_count' => 0,
                'storage_used' => 0,
                'api_calls_this_month' => 0
            ],
            'limits' => [
                'max_users' => 3,
                'max_products' => -1,
                'max_storage_mb' => 5000,
                'max_api_calls_per_month' => 10000
            ],
            'status' => 'active'
        ]);
    }
}
