<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Tenant;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tenant = Tenant::where('business_name', 'Premium Meats Inc.')->first();
        
        if (!$tenant) {
            return;
        }

        $products = [
            // Prime Cuts
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_RIB_STEAK',
                'name' => 'Prime Rib Steak',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu Prime Rib Steak - Grade 1',
                'pricing' => [
                    'price_per_unit' => 2870,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 2500
                ],
                'inventory' => [
                    'current_stock' => 50,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'physical_attributes' => [
                    'weight_range' => '300-500g',
                    'cut_thickness' => '2.5cm',
                    'marbling_score' => 'A5'
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_RIBEYE',
                'name' => 'Ribeye',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu Ribeye - Grade 1',
                'pricing' => [
                    'price_per_unit' => 3570,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 3200
                ],
                'inventory' => [
                    'current_stock' => 45,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'physical_attributes' => [
                    'weight_range' => '250-400g',
                    'cut_thickness' => '3cm',
                    'marbling_score' => 'A5'
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_SHORTLOIN',
                'name' => 'Shortloin Slab',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu Shortloin Slab - Grade 1',
                'pricing' => [
                    'price_per_unit' => 2670,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 2400
                ],
                'inventory' => [
                    'current_stock' => 30,
                    'reorder_level' => 8,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_TENDERLOIN',
                'name' => 'Tenderloin',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu Tenderloin - Grade 1',
                'pricing' => [
                    'price_per_unit' => 4020,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 3600
                ],
                'inventory' => [
                    'current_stock' => 25,
                    'reorder_level' => 5,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'physical_attributes' => [
                    'weight_range' => '200-300g',
                    'marbling_score' => 'A5'
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_STRIPLOIN',
                'name' => 'Striploin',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu Striploin - Grade 1',
                'pricing' => [
                    'price_per_unit' => 2870,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 2600
                ],
                'inventory' => [
                    'current_stock' => 35,
                    'reorder_level' => 8,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_PORTERHOUSE',
                'name' => 'Porterhouse',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu Porterhouse - Grade 1',
                'pricing' => [
                    'price_per_unit' => 2670,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 2400
                ],
                'inventory' => [
                    'current_stock' => 20,
                    'reorder_level' => 5,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'physical_attributes' => [
                    'weight_range' => '500-700g',
                    'marbling_score' => 'A5'
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PRIME_TBONE',
                'name' => 'T-Bone',
                'category' => 'beef',
                'subcategory' => 'prime',
                'description' => 'Premium Wagyu T-Bone Steak - Grade 1',
                'pricing' => [
                    'price_per_unit' => 2470,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 2200
                ],
                'inventory' => [
                    'current_stock' => 22,
                    'reorder_level' => 6,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'physical_attributes' => [
                    'weight_range' => '450-600g',
                    'marbling_score' => 'A5'
                ],
                'status' => 'active'
            ],

            // Premium Cuts
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_OYSTER_BLADE',
                'name' => 'Oyster blade',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Oyster Blade - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1720,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1500
                ],
                'inventory' => [
                    'current_stock' => 40,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_FLAT_IRON',
                'name' => 'Flat iron steak',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Flat Iron Steak - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 2120,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1900
                ],
                'inventory' => [
                    'current_stock' => 35,
                    'reorder_level' => 8,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_BRISKET',
                'name' => 'Brisket',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Brisket - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 980,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 850
                ],
                'inventory' => [
                    'current_stock' => 60,
                    'reorder_level' => 15,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_CHUCK_ROLL',
                'name' => 'Chuck Roll',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Chuck Roll - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1870,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1650
                ],
                'inventory' => [
                    'current_stock' => 45,
                    'reorder_level' => 12,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_SHORT_PLATE',
                'name' => 'SHORT PLATE',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Short Plate - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1020,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 900
                ],
                'inventory' => [
                    'current_stock' => 50,
                    'reorder_level' => 12,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_BONELESS_SHORT_PLATE',
                'name' => 'Boneless Short Plate',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Boneless Short Plate - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1270,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1100
                ],
                'inventory' => [
                    'current_stock' => 38,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_TENDERLOIN_TIP',
                'name' => 'Tenderloin Tip',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Tenderloin Tip - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1920,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1700
                ],
                'inventory' => [
                    'current_stock' => 25,
                    'reorder_level' => 6,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_SIRLOIN',
                'name' => 'Sirloin',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Sirloin - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1720,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1500
                ],
                'inventory' => [
                    'current_stock' => 42,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_TRI_TIP',
                'name' => 'Tri-tip',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Tri-tip - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1720,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1500
                ],
                'inventory' => [
                    'current_stock' => 30,
                    'reorder_level' => 8,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_FLANK_STEAK',
                'name' => 'Flank Steak',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Flank Steak - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 1870,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 1650
                ],
                'inventory' => [
                    'current_stock' => 28,
                    'reorder_level' => 7,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'PREM_FLANK_WHOLE',
                'name' => 'Flank Whole',
                'category' => 'beef',
                'subcategory' => 'premium',
                'description' => 'Wagyu Whole Flank - Premium Grade',
                'pricing' => [
                    'price_per_unit' => 885,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 750
                ],
                'inventory' => [
                    'current_stock' => 35,
                    'reorder_level' => 9,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],

            // Select Cuts
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_CHUCK_TENDER',
                'name' => 'Chuck Tender',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Chuck Tender - Select Grade',
                'pricing' => [
                    'price_per_unit' => 770,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 650
                ],
                'inventory' => [
                    'current_stock' => 55,
                    'reorder_level' => 15,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_BOLAR_BLADE',
                'name' => 'Bolar Blade',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Bolar Blade - Select Grade',
                'pricing' => [
                    'price_per_unit' => 1060,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 900
                ],
                'inventory' => [
                    'current_stock' => 48,
                    'reorder_level' => 12,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_SHORT_RIBS',
                'name' => 'Short Ribs',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Short Ribs - Select Grade',
                'pricing' => [
                    'price_per_unit' => 855,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 720
                ],
                'inventory' => [
                    'current_stock' => 40,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_BONELESS_SHORT_RIB',
                'name' => 'Boneless Short Rib',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Boneless Short Rib - Select Grade',
                'pricing' => [
                    'price_per_unit' => 1050,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 900
                ],
                'inventory' => [
                    'current_stock' => 32,
                    'reorder_level' => 8,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_SIRLOIN_TIP',
                'name' => 'Sirloin Tip',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Sirloin Tip - Select Grade',
                'pricing' => [
                    'price_per_unit' => 970,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 820
                ],
                'inventory' => [
                    'current_stock' => 38,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_TOP_ROUND',
                'name' => 'Top Round',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Top Round - Select Grade',
                'pricing' => [
                    'price_per_unit' => 960,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 810
                ],
                'inventory' => [
                    'current_stock' => 45,
                    'reorder_level' => 12,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'SEL_SILVERSIDE',
                'name' => 'Silverside',
                'category' => 'beef',
                'subcategory' => 'select',
                'description' => 'Wagyu Silverside - Select Grade',
                'pricing' => [
                    'price_per_unit' => 880,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 740
                ],
                'inventory' => [
                    'current_stock' => 42,
                    'reorder_level' => 11,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],

            // Choice Cuts
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'CHO_NECK_MEAT',
                'name' => 'Neck Meat',
                'category' => 'beef',
                'subcategory' => 'choice',
                'description' => 'Wagyu Neck Meat - Choice Grade',
                'pricing' => [
                    'price_per_unit' => 770,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 650
                ],
                'inventory' => [
                    'current_stock' => 60,
                    'reorder_level' => 15,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'CHO_HUMP_ROAST',
                'name' => 'Hump Roast',
                'category' => 'beef',
                'subcategory' => 'choice',
                'description' => 'Wagyu Hump Roast - Choice Grade',
                'pricing' => [
                    'price_per_unit' => 770,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 650
                ],
                'inventory' => [
                    'current_stock' => 35,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'CHO_SHANK_BI',
                'name' => 'Shank BI',
                'category' => 'beef',
                'subcategory' => 'choice',
                'description' => 'Wagyu Shank BI - Choice Grade',
                'pricing' => [
                    'price_per_unit' => 620,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 520
                ],
                'inventory' => [
                    'current_stock' => 50,
                    'reorder_level' => 12,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'CHO_EYE_ROUND',
                'name' => 'Eye Round',
                'category' => 'beef',
                'subcategory' => 'choice',
                'description' => 'Wagyu Eye Round - Choice Grade',
                'pricing' => [
                    'price_per_unit' => 770,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 650
                ],
                'inventory' => [
                    'current_stock' => 40,
                    'reorder_level' => 10,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'CHO_SHIN_SHANK',
                'name' => 'Shin/Shank Boneless',
                'category' => 'beef',
                'subcategory' => 'choice',
                'description' => 'Wagyu Shin/Shank Boneless - Choice Grade',
                'pricing' => [
                    'price_per_unit' => 670,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 560
                ],
                'inventory' => [
                    'current_stock' => 45,
                    'reorder_level' => 11,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],

            // Byproducts
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'BY_NECK_BONES',
                'name' => 'Neck Bones',
                'category' => 'byproduct',
                'subcategory' => 'bones',
                'description' => 'Wagyu Neck Bones',
                'pricing' => [
                    'price_per_unit' => 410,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 350
                ],
                'inventory' => [
                    'current_stock' => 80,
                    'reorder_level' => 20,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'BY_SOUP_BONES',
                'name' => 'Soup Bones',
                'category' => 'byproduct',
                'subcategory' => 'bones',
                'description' => 'Wagyu Soup Bones',
                'pricing' => [
                    'price_per_unit' => 220,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 180
                ],
                'inventory' => [
                    'current_stock' => 100,
                    'reorder_level' => 25,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'BY_BONE_MARROW',
                'name' => 'Bone Marrow',
                'category' => 'byproduct',
                'subcategory' => 'marrow',
                'description' => 'Wagyu Bone Marrow',
                'pricing' => [
                    'price_per_unit' => 440,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 380
                ],
                'inventory' => [
                    'current_stock' => 30,
                    'reorder_level' => 8,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'product_code' => 'BY_FATS',
                'name' => 'Fats',
                'category' => 'byproduct',
                'subcategory' => 'fat',
                'description' => 'Wagyu Beef Fat',
                'pricing' => [
                    'price_per_unit' => 340,
                    'unit_type' => 'kg',
                    'tax_rate' => 8.25,
                    'cost_per_unit' => 290
                ],
                'inventory' => [
                    'current_stock' => 70,
                    'reorder_level' => 18,
                    'unit_of_measure' => 'kg'
                ],
                'batch_tracking' => [
                    'enabled' => true,
                    'track_expiry' => true,
                    'track_temperature' => true
                ],
                'status' => 'active'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Update tenant usage
        $tenant->updateUsage('products', count($products));
    }
}
