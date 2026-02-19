<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Tenant;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $customers = [
                [
                    'tenant_id' => $tenant->tenant_id,
                    'customer_code' => 'CUST' . str_pad(($tenant->id * 100 + 1), 6, '0', STR_PAD_LEFT),
                    'personal_info' => [
                        'first_name' => 'Robert',
                        'last_name' => 'Johnson',
                        'email' => 'robert.johnson@email.com',
                        'phone' => '+1-555-1001',
                        'address' => [
                            'street' => '123 Main St',
                            'city' => 'Meatville',
                            'state' => 'TX',
                            'zip_code' => '75001',
                            'country' => 'US'
                        ]
                    ],
                    'preferences' => [
                        'preferred_contact_method' => 'email',
                        'marketing_consent' => [
                            'email' => true,
                            'sms' => false
                        ]
                    ],
                    'loyalty' => [
                        'tier' => 'bronze',
                        'points_balance' => 150,
                        'total_spent' => 2500,
                        'join_date' => now()->subMonths(6)
                    ],
                    'purchasing_history' => [
                        'total_orders' => 12,
                        'total_spent' => 2500,
                        'average_order_value' => 208.33,
                        'last_purchase_date' => now()->subDays(5)
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'customer_code' => 'CUST' . str_pad(($tenant->id * 100 + 2), 6, '0', STR_PAD_LEFT),
                    'personal_info' => [
                        'first_name' => 'Maria',
                        'last_name' => 'Garcia',
                        'email' => 'maria.garcia@email.com',
                        'phone' => '+1-555-1002',
                        'address' => [
                            'street' => '456 Oak Ave',
                            'city' => 'Butchertown',
                            'state' => 'CA',
                            'zip_code' => '90210',
                            'country' => 'US'
                        ]
                    ],
                    'preferences' => [
                        'preferred_contact_method' => 'sms',
                        'marketing_consent' => [
                            'email' => true,
                            'sms' => true
                        ]
                    ],
                    'loyalty' => [
                        'tier' => 'silver',
                        'points_balance' => 850,
                        'total_spent' => 8500,
                        'join_date' => now()->subYear(1)
                    ],
                    'purchasing_history' => [
                        'total_orders' => 35,
                        'total_spent' => 8500,
                        'average_order_value' => 242.86,
                        'last_purchase_date' => now()->subDays(2)
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'customer_code' => 'CUST' . str_pad(($tenant->id * 100 + 3), 6, '0', STR_PAD_LEFT),
                    'personal_info' => [
                        'first_name' => 'James',
                        'last_name' => 'Wilson',
                        'email' => 'james.wilson@email.com',
                        'phone' => '+1-555-1003',
                        'address' => [
                            'street' => '789 Pine Rd',
                            'city' => 'Steak City',
                            'state' => 'NY',
                            'zip_code' => '10001',
                            'country' => 'US'
                        ]
                    ],
                    'preferences' => [
                        'preferred_contact_method' => 'email',
                        'marketing_consent' => [
                            'email' => false,
                            'sms' => false
                        ]
                    ],
                    'loyalty' => [
                        'tier' => 'gold',
                        'points_balance' => 2200,
                        'total_spent' => 15000,
                        'join_date' => now()->subYears(2)
                    ],
                    'purchasing_history' => [
                        'total_orders' => 68,
                        'total_spent' => 15000,
                        'average_order_value' => 220.59,
                        'last_purchase_date' => now()->subDays(1)
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'customer_code' => 'CUST' . str_pad(($tenant->id * 100 + 4), 6, '0', STR_PAD_LEFT),
                    'personal_info' => [
                        'first_name' => 'Patricia',
                        'last_name' => 'Brown',
                        'email' => 'patricia.brown@email.com',
                        'phone' => '+1-555-1004',
                        'address' => [
                            'street' => '321 Elm St',
                            'city' => 'Grill Town',
                            'state' => 'FL',
                            'zip_code' => '33101',
                            'country' => 'US'
                        ]
                    ],
                    'preferences' => [
                        'preferred_contact_method' => 'email',
                        'marketing_consent' => [
                            'email' => true,
                            'sms' => false
                        ]
                    ],
                    'loyalty' => [
                        'tier' => 'platinum',
                        'points_balance' => 5500,
                        'total_spent' => 35000,
                        'join_date' => now()->subYears(3)
                    ],
                    'purchasing_history' => [
                        'total_orders' => 145,
                        'total_spent' => 35000,
                        'average_order_value' => 241.38,
                        'last_purchase_date' => now()->subHours(3)
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'customer_code' => 'CUST' . str_pad(($tenant->id * 100 + 5), 6, '0', STR_PAD_LEFT),
                    'personal_info' => [
                        'first_name' => 'David',
                        'last_name' => 'Lee',
                        'email' => 'david.lee@email.com',
                        'phone' => '+1-555-1005',
                        'address' => [
                            'street' => '654 Maple Dr',
                            'city' => 'BBQ Heights',
                            'state' => 'TX',
                            'zip_code' => '75201',
                            'country' => 'US'
                        ]
                    ],
                    'preferences' => [
                        'preferred_contact_method' => 'sms',
                        'marketing_consent' => [
                            'email' => true,
                            'sms' => true
                        ]
                    ],
                    'loyalty' => [
                        'tier' => 'bronze',
                        'points_balance' => 75,
                        'total_spent' => 1200,
                        'join_date' => now()->subMonths(3)
                    ],
                    'purchasing_history' => [
                        'total_orders' => 6,
                        'total_spent' => 1200,
                        'average_order_value' => 200,
                        'last_purchase_date' => now()->subWeeks(2)
                    ],
                    'status' => 'active'
                ]
            ];

            foreach ($customers as $customer) {
                Customer::create($customer);
            }

            // Update tenant usage for customers
            $tenant->updateUsage('customers', count($customers));
        }
    }
}
