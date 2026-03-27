<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KitayamaRetail2025Seeder extends Seeder
{
    public function run(): void
    {
        $connection = 'tenant';

        if (!Schema::connection($connection)->hasTable('products')
            || !Schema::connection($connection)->hasTable('price_lists')
            || !Schema::connection($connection)->hasTable('product_prices')
            || !Schema::connection($connection)->hasTable('categories')) {
            return;
        }

        $tenantId = (string) (config('seeding.tenant_id') ?? '');
        if ($tenantId === '' && function_exists('tenant') && tenant()) {
            $tenantId = (string) tenant()->tenant_id;
        }

        $uomKg = null;
        if (Schema::connection($connection)->hasTable('units_of_measure')) {
            $uomKg = UnitOfMeasure::on($connection)->updateOrCreate(
                ['code' => 'kg'],
                ['name' => 'Kilogram', 'precision' => 3]
            );
        }

        $catalog = [
            ['name' => 'Prime Rib Steak', 'category' => 'Prime', 'uom' => 'kg', 'price' => 2870],
            ['name' => 'Ribeye', 'category' => 'Prime', 'uom' => 'kg', 'price' => 3570],
            ['name' => 'Shortloin Slab', 'category' => 'Prime', 'uom' => 'kg', 'price' => 2670],
            ['name' => 'Tenderloin', 'category' => 'Prime', 'uom' => 'kg', 'price' => 4020],
            ['name' => 'Striploin', 'category' => 'Prime', 'uom' => 'kg', 'price' => 2870],
            ['name' => 'Porterhouse', 'category' => 'Prime', 'uom' => 'kg', 'price' => 2670],
            ['name' => 'T-Bone', 'category' => 'Prime', 'uom' => 'kg', 'price' => 2470],

            ['name' => 'Oyster blade', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1720],
            ['name' => 'Flat iron steak', 'category' => 'Premium', 'uom' => 'kg', 'price' => 2120],
            ['name' => 'Brisket', 'category' => 'Premium', 'uom' => 'kg', 'price' => 980],
            ['name' => 'Chuck Roll', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1870],
            ['name' => 'SHORT PLATE', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1020],
            ['name' => 'Boneless Short Plate', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1270],
            ['name' => 'Tenderloin Tip', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1920],
            ['name' => 'Sirloin', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1720],
            ['name' => 'Tri-tip', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1720],
            ['name' => 'Flank Steak', 'category' => 'Premium', 'uom' => 'kg', 'price' => 1870],
            ['name' => 'Flank Whole', 'category' => 'Premium', 'uom' => 'kg', 'price' => 885],

            ['name' => 'Chuck Tender', 'category' => 'Select', 'uom' => 'kg', 'price' => 770],
            ['name' => 'Bolar Blade', 'category' => 'Select', 'uom' => 'kg', 'price' => 1060],
            ['name' => 'Short Ribs', 'category' => 'Select', 'uom' => 'kg', 'price' => 855],
            ['name' => 'Boneless Short Rib', 'category' => 'Select', 'uom' => 'kg', 'price' => 1050],
            ['name' => 'Sirloin Tip', 'category' => 'Select', 'uom' => 'kg', 'price' => 970],
            ['name' => 'Top Round', 'category' => 'Select', 'uom' => 'kg', 'price' => 960],
            ['name' => 'Silverside', 'category' => 'Select', 'uom' => 'kg', 'price' => 880],

            ['name' => 'Neck Meat', 'category' => 'Choice', 'uom' => 'kg', 'price' => 770],
            ['name' => 'Hump Roast', 'category' => 'Choice', 'uom' => 'kg', 'price' => 770],
            ['name' => 'Shank BI', 'category' => 'Choice', 'uom' => 'kg', 'price' => 620],
            ['name' => 'Eye Round', 'category' => 'Choice', 'uom' => 'kg', 'price' => 770],
            ['name' => 'Shin/Shank Boneless', 'category' => 'Choice', 'uom' => 'kg', 'price' => 670],

            ['name' => 'Neck Bones', 'category' => 'Byproduct', 'uom' => 'kg', 'price' => 410],
            ['name' => 'Soup Bones', 'category' => 'Byproduct', 'uom' => 'kg', 'price' => 220],
            ['name' => 'Bone Marrow', 'category' => 'Byproduct', 'uom' => 'kg', 'price' => 440],
            ['name' => 'Fats', 'category' => 'Byproduct', 'uom' => 'kg', 'price' => 340],
        ];

        $categoryOrder = ['Prime', 'Premium', 'Select', 'Choice', 'Byproduct'];
        $categories = [];

        foreach ($categoryOrder as $index => $categoryName) {
            $categories[$categoryName] = Category::on($connection)->updateOrCreate(
                ['code' => Str::upper(Str::slug($categoryName, '_'))],
                [
                    'name' => $categoryName,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        $priceListPayload = [
            'code' => 'RETAIL_2025',
            'name' => 'Retail 2025',
            'channel' => 'retail',
            'currency' => 'PHP',
            'status' => 'published',
            'effective_from' => '2025-01-01 00:00:00',
            'effective_to' => null,
            'published_at' => now(),
            'published_by' => null,
        ];

        if (Schema::connection($connection)->hasColumn('price_lists', 'tenant_id')) {
            $priceListPayload['tenant_id'] = $tenantId !== '' ? $tenantId : null;
        }

        $priceList = PriceList::on($connection)->updateOrCreate(
            ['code' => 'RETAIL_2025', 'tenant_id' => $priceListPayload['tenant_id'] ?? null],
            $priceListPayload
        );

        foreach ($catalog as $row) {
            $productPayload = [
                'name' => $row['name'],
                'description' => 'Kitayama Wagyu Beef - Retail 2025',
                'category' => 'beef',
                'subcategory' => strtolower($row['category']),
                'category_id' => $categories[$row['category']]->id,
                'uom_id' => $uomKg?->id,
                'pricing' => ['unit_type' => $row['uom']],
                'inventory' => [
                    'current_stock' => 0,
                    'reorder_level' => 0,
                    'unit_of_measure' => 'kg',
                ],
                'batch_tracking' => ['enabled' => true],
                'status' => 'active',
                'is_active' => true,
            ];

            if (Schema::connection($connection)->hasColumn('products', 'tenant_id')) {
                $productPayload['tenant_id'] = $tenantId !== '' ? $tenantId : null;
            }

            $code = Str::upper(Str::slug($row['name'], '_'));
            $productLookup = ['product_code' => $code];
            if (Schema::connection($connection)->hasColumn('products', 'tenant_id')) {
                $productLookup['tenant_id'] = $tenantId !== '' ? $tenantId : null;
            }

            $product = Product::on($connection)->updateOrCreate(
                $productLookup,
                $productPayload
            );

            $productPricePayload = [
                'price_list_id' => $priceList->id,
                'product_id' => $product->id,
                'uom' => 'kg',
                'price' => (float) $row['price'],
            ];

            if (Schema::connection($connection)->hasColumn('product_prices', 'tenant_id')) {
                $productPricePayload['tenant_id'] = $tenantId !== '' ? $tenantId : null;
            }

            ProductPrice::on($connection)->updateOrCreate(
                [
                    'price_list_id' => $priceList->id,
                    'product_id' => $product->id,
                    'uom' => 'kg',
                ],
                $productPricePayload
            );
        }
    }
}
