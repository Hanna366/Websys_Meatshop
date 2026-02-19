<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Tenant;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $suppliers = [
                [
                    'tenant_id' => $tenant->tenant_id,
                    'supplier_code' => 'SUP' . str_pad(($tenant->id * 100 + 1), 6, '0', STR_PAD_LEFT),
                    'business_info' => [
                        'name' => 'Premium Wagyu Farms',
                        'contact_person' => 'John Smith',
                        'email' => 'john@premiumwagyu.com',
                        'phone' => '+1-555-2001',
                        'fax' => '+1-555-2002',
                        'website' => 'https://premiumwagyu.com'
                    ],
                    'address' => [
                        'street' => '1000 Ranch Road',
                        'city' => 'Wagyu Valley',
                        'state' => 'TX',
                        'zip_code' => '75001',
                        'country' => 'US'
                    ],
                    'business_details' => [
                        'tax_id' => 'TX-123456789',
                        'business_license' => 'BL-2024-001',
                        'years_in_business' => 15,
                        'certifications' => [
                            'USDA Organic',
                            'Halal Certified',
                            'Animal Welfare Approved'
                        ]
                    ],
                    'product_categories' => ['beef'],
                    'payment_terms' => [
                        'method' => 'net_30',
                        'credit_limit' => 50000,
                        'due_days' => 30
                    ],
                    'delivery_info' => [
                        'delivery_days' => ['Monday', 'Wednesday', 'Friday'],
                        'minimum_order' => 100,
                        'delivery_fee' => 50,
                        'delivery_radius' => 200
                    ],
                    'quality_standards' => [
                        'grade_requirements' => ['A5', 'A4'],
                        'inspection_required' => true,
                        'temperature_control' => true,
                        'traceability' => true
                    ],
                    'performance_metrics' => [
                        'quality_score' => 95,
                        'delivery_performance' => 98,
                        'price_competitiveness' => 85,
                        'reliability' => 92,
                        'last_updated' => now()
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'supplier_code' => 'SUP' . str_pad(($tenant->id * 100 + 2), 6, '0', STR_PAD_LEFT),
                    'business_info' => [
                        'name' => 'Local Beef Co-op',
                        'contact_person' => 'Maria Rodriguez',
                        'email' => 'maria@localbeef.com',
                        'phone' => '+1-555-2003',
                        'fax' => '+1-555-2004',
                        'website' => 'https://localbeef.com'
                    ],
                    'address' => [
                        'street' => '500 Farm Lane',
                        'city' => 'Cattle Town',
                        'state' => 'CA',
                        'zip_code' => '90210',
                        'country' => 'US'
                    ],
                    'business_details' => [
                        'tax_id' => 'CA-987654321',
                        'business_license' => 'BL-2023-045',
                        'years_in_business' => 25,
                        'certifications' => [
                            'USDA Prime',
                            'Grass Fed Certified',
                            'Local Farm Verified'
                        ]
                    ],
                    'product_categories' => ['beef'],
                    'payment_terms' => [
                        'method' => 'net_15',
                        'credit_limit' => 25000,
                        'due_days' => 15
                    ],
                    'delivery_info' => [
                        'delivery_days' => ['Tuesday', 'Thursday'],
                        'minimum_order' => 50,
                        'delivery_fee' => 25,
                        'delivery_radius' => 100
                    ],
                    'quality_standards' => [
                        'grade_requirements' => ['Prime', 'Choice'],
                        'inspection_required' => true,
                        'temperature_control' => true,
                        'traceability' => true
                    ],
                    'performance_metrics' => [
                        'quality_score' => 88,
                        'delivery_performance' => 95,
                        'price_competitiveness' => 92,
                        'reliability' => 90,
                        'last_updated' => now()
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'supplier_code' => 'SUP' . str_pad(($tenant->id * 100 + 3), 6, '0', STR_PAD_LEFT),
                    'business_info' => [
                        'name' => 'International Meat Imports',
                        'contact_person' => 'Chen Wei',
                        'email' => 'chen@internationalmeat.com',
                        'phone' => '+1-555-2005',
                        'fax' => '+1-555-2006',
                        'website' => 'https://internationalmeat.com'
                    ],
                    'address' => [
                        'street' => '2500 Export Blvd',
                        'city' => 'Port City',
                        'state' => 'NY',
                        'zip_code' => '10001',
                        'country' => 'US'
                    ],
                    'business_details' => [
                        'tax_id' => 'NY-456789123',
                        'business_license' => 'BL-2022-089',
                        'years_in_business' => 30,
                        'certifications' => [
                            'HACCP Certified',
                            'ISO 9001',
                            'Global Food Safety'
                        ]
                    ],
                    'product_categories' => ['beef', 'pork', 'chicken'],
                    'payment_terms' => [
                        'method' => 'net_45',
                        'credit_limit' => 100000,
                        'due_days' => 45
                    ],
                    'delivery_info' => [
                        'delivery_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                        'minimum_order' => 500,
                        'delivery_fee' => 100,
                        'delivery_radius' => 500
                    ],
                    'quality_standards' => [
                        'grade_requirements' => ['All Grades'],
                        'inspection_required' => true,
                        'temperature_control' => true,
                        'traceability' => true
                    ],
                    'performance_metrics' => [
                        'quality_score' => 82,
                        'delivery_performance' => 88,
                        'price_competitiveness' => 78,
                        'reliability' => 85,
                        'last_updated' => now()
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'supplier_code' => 'SUP' . str_pad(($tenant->id * 100 + 4), 6, '0', STR_PAD_LEFT),
                    'business_info' => [
                        'name' => 'Organic Ranch Supply',
                        'contact_person' => 'Sarah Green',
                        'email' => 'sarah@organicranch.com',
                        'phone' => '+1-555-2007',
                        'fax' => '+1-555-2008',
                        'website' => 'https://organicranch.com'
                    ],
                    'address' => [
                        'street' => '750 Green Acres',
                        'city' => 'Organic Valley',
                        'state' => 'CO',
                        'zip_code' => '80201',
                        'country' => 'US'
                    ],
                    'business_details' => [
                        'tax_id' => 'CO-789123456',
                        'business_license' => 'BL-2021-067',
                        'years_in_business' => 12,
                        'certifications' => [
                            'USDA Organic',
                            'Non-GMO Project',
                            'Certified Humane'
                        ]
                    ],
                    'product_categories' => ['beef', 'lamb'],
                    'payment_terms' => [
                        'method' => 'net_30',
                        'credit_limit' => 30000,
                        'due_days' => 30
                    ],
                    'delivery_info' => [
                        'delivery_days' => ['Wednesday', 'Saturday'],
                        'minimum_order' => 75,
                        'delivery_fee' => 40,
                        'delivery_radius' => 150
                    ],
                    'quality_standards' => [
                        'grade_requirements' => ['Choice', 'Select'],
                        'inspection_required' => true,
                        'temperature_control' => true,
                        'traceability' => true
                    ],
                    'performance_metrics' => [
                        'quality_score' => 90,
                        'delivery_performance' => 92,
                        'price_competitiveness' => 88,
                        'reliability' => 89,
                        'last_updated' => now()
                    ],
                    'status' => 'active'
                ],
                [
                    'tenant_id' => $tenant->tenant_id,
                    'supplier_code' => 'SUP' . str_pad(($tenant->id * 100 + 5), 6, '0', STR_PAD_LEFT),
                    'business_info' => [
                        'name' => 'Regional Distribution Center',
                        'contact_person' => 'Mike Johnson',
                        'email' => 'mike@regionaldist.com',
                        'phone' => '+1-555-2009',
                        'fax' => '+1-555-2010',
                        'website' => 'https://regionaldist.com'
                    ],
                    'address' => [
                        'street' => '1500 Warehouse Way',
                        'city' => 'Distribution City',
                        'state' => 'IL',
                        'zip_code' => '60601',
                        'country' => 'US'
                    ],
                    'business_details' => [
                        'tax_id' => 'IL-321654987',
                        'business_license' => 'BL-2020-123',
                        'years_in_business' => 20,
                        'certifications' => [
                            'FDA Registered',
                            'Cold Chain Certified',
                            'Food Safety Modernization Act'
                        ]
                    ],
                    'product_categories' => ['beef', 'pork', 'chicken', 'lamb', 'seafood'],
                    'payment_terms' => [
                        'method' => 'net_60',
                        'credit_limit' => 75000,
                        'due_days' => 60
                    ],
                    'delivery_info' => [
                        'delivery_days' => ['Daily'],
                        'minimum_order' => 200,
                        'delivery_fee' => 75,
                        'delivery_radius' => 300
                    ],
                    'quality_standards' => [
                        'grade_requirements' => ['All Grades'],
                        'inspection_required' => true,
                        'temperature_control' => true,
                        'traceability' => true
                    ],
                    'performance_metrics' => [
                        'quality_score' => 85,
                        'delivery_performance' => 94,
                        'price_competitiveness' => 80,
                        'reliability' => 87,
                        'last_updated' => now()
                    ],
                    'status' => 'active'
                ]
            ];

            foreach ($suppliers as $supplier) {
                Supplier::create($supplier);
            }

            // Update tenant usage for suppliers
            $tenant->updateUsage('suppliers', count($suppliers));
        }
    }
}
