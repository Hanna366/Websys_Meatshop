<?php

namespace App\Console\Commands;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Tenant;
use App\Models\UnitOfMeasure;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportPriceListCommand extends Command
{
    protected $signature = 'pricing:import
        {tenant_id : Tenant ID or tenant code}
        {file : Absolute or relative path to CSV/XLS/XLSX}
        {--name= : Price list name, e.g. Retail 2025}
        {--code= : Price list code, e.g. retail-2025}
        {--channel=retail : Price channel}
        {--effective-from= : Effective from datetime (Y-m-d H:i:s)}
        {--publish : Publish after import}';

    protected $description = 'Import product prices from spreadsheet into versioned price lists.';

    public function handle(): int
    {
        $tenantIdentifier = (string) $this->argument('tenant_id');
        $filePath = (string) $this->argument('file');

        $tenant = Tenant::query()
            ->where('tenant_id', $tenantIdentifier)
            ->orWhere('id', $tenantIdentifier)
            ->first();

        if (!$tenant) {
            $this->error('Tenant not found.');
            return self::FAILURE;
        }

        if (!is_file($filePath)) {
            $resolved = base_path($filePath);
            if (!is_file($resolved)) {
                $this->error('Spreadsheet file not found.');
                return self::FAILURE;
            }
            $filePath = $resolved;
        }

        $rows = Excel::toArray([], $filePath)[0] ?? [];
        if (count($rows) < 2) {
            $this->error('Spreadsheet is empty or missing data rows.');
            return self::FAILURE;
        }

        $header = array_map(fn ($h) => $this->normalizeHeader((string) $h), $rows[0]);
        $requiredHeaders = ['product_name', 'category', 'unit_of_measure', 'price'];
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $header, true)) {
                $this->error("Missing required column: {$required}");
                return self::FAILURE;
            }
        }

        $name = (string) ($this->option('name') ?: ('Retail ' . now()->year));
        $code = (string) ($this->option('code') ?: Str::slug($name));
        $channel = (string) $this->option('channel');
        $effectiveFrom = $this->option('effective-from')
            ? Carbon::parse((string) $this->option('effective-from'))
            : now();

        tenancy()->initialize($tenant);

        try {
            DB::transaction(function () use ($rows, $header, $tenant, $name, $code, $channel, $effectiveFrom) {
                $priceList = PriceList::create([
                    'tenant_id' => $tenant->tenant_id,
                    'code' => $code,
                    'name' => $name,
                    'channel' => $channel,
                    'currency' => 'PHP',
                    'status' => 'draft',
                    'effective_from' => $effectiveFrom,
                ]);

                UnitOfMeasure::firstOrCreate(
                    ['code' => 'kg'],
                    ['name' => 'Kilogram', 'precision' => 3]
                );

                $rowsImported = 0;
                $errors = [];

                foreach (array_slice($rows, 1) as $line => $row) {
                    $record = $this->rowToRecord($row, $header);
                    if (!$this->isValidRecord($record)) {
                        $errors[] = 'Row ' . ($line + 2) . ': invalid data.';
                        continue;
                    }

                    $categoryCode = Str::slug((string) $record['category'], '-');
                    $category = ProductCategory::firstOrCreate(
                        ['tenant_id' => $tenant->tenant_id, 'code' => $categoryCode],
                        ['name' => (string) $record['category'], 'is_active' => true]
                    );

                    $uomCode = Str::lower(trim((string) $record['unit_of_measure']));
                    $uom = UnitOfMeasure::firstOrCreate(
                        ['code' => $uomCode],
                        ['name' => Str::title($uomCode), 'precision' => 3]
                    );

                    $productCode = trim((string) ($record['product_code'] ?? ''));
                    $productName = trim((string) $record['product_name']);

                    $productQuery = Product::query();
                    if (Schema::hasColumn('products', 'tenant_id')) {
                        $productQuery->where('tenant_id', $tenant->tenant_id);
                    }

                    if ($productCode !== '' && Schema::hasColumn('products', 'product_code')) {
                        $productQuery->where('product_code', $productCode);
                    } else {
                        $productQuery->where('name', $productName);
                    }

                    $product = $productQuery->first();

                    if (!$product) {
                        $product = Product::create(array_filter([
                            'tenant_id' => Schema::hasColumn('products', 'tenant_id') ? $tenant->tenant_id : null,
                            'product_code' => $productCode !== '' ? $productCode : $this->generateProductCode($productName),
                            'name' => $productName,
                            'description' => null,
                            'category' => Schema::hasColumn('products', 'category') ? 'other' : null,
                            'subcategory' => null,
                            'category_id' => Schema::hasColumn('products', 'category_id') ? $category->id : null,
                            'uom_id' => Schema::hasColumn('products', 'uom_id') ? $uom->id : null,
                            'pricing' => Schema::hasColumn('products', 'pricing') ? ['price_per_unit' => (float) $record['price'], 'unit_type' => $uomCode] : null,
                            'inventory' => Schema::hasColumn('products', 'inventory') ? ['current_stock' => 0, 'reorder_level' => 0, 'unit_of_measure' => $uomCode] : null,
                            'batch_tracking' => Schema::hasColumn('products', 'batch_tracking') ? ['enabled' => true] : null,
                            'status' => Schema::hasColumn('products', 'status') ? 'active' : null,
                            'is_active' => Schema::hasColumn('products', 'is_active') ? true : null,
                        ], static fn ($v) => $v !== null));
                    } else {
                        if (Schema::hasColumn('products', 'category_id')) {
                            $product->category_id = $category->id;
                        }
                        if (Schema::hasColumn('products', 'uom_id')) {
                            $product->uom_id = $uom->id;
                        }
                        if (Schema::hasColumn('products', 'pricing')) {
                            $pricing = $product->pricing ?? [];
                            $pricing['price_per_unit'] = (float) $record['price'];
                            $pricing['unit_type'] = $uomCode;
                            $product->pricing = $pricing;
                        }
                        $product->save();
                    }

                    PriceListItem::updateOrCreate(
                        [
                            'price_list_id' => $priceList->id,
                            'product_id' => $product->id,
                            'min_qty' => null,
                            'max_qty' => null,
                        ],
                        [
                            'tenant_id' => $tenant->tenant_id,
                            'price' => (float) $record['price'],
                        ]
                    );

                    $rowsImported++;
                }

                if ($this->option('publish')) {
                    $priceList->status = 'published';
                    $priceList->published_at = now();
                    $priceList->save();
                }

                $this->info("Imported {$rowsImported} rows into price list {$priceList->name} ({$priceList->code}).");
                if (!empty($errors)) {
                    $this->warn('Skipped rows:');
                    foreach ($errors as $error) {
                        $this->line('- ' . $error);
                    }
                }
            });
        } finally {
            tenancy()->end();
        }

        return self::SUCCESS;
    }

    private function normalizeHeader(string $header): string
    {
        $header = Str::of($header)->lower()->trim()->replace([' ', '-'], '_')->toString();

        return match ($header) {
            'product', 'name' => 'product_name',
            'uom', 'unit', 'unit_measure', 'unit_of_measurement' => 'unit_of_measure',
            default => $header,
        };
    }

    private function rowToRecord(array $row, array $header): array
    {
        $record = [];
        foreach ($header as $index => $column) {
            $record[$column] = $row[$index] ?? null;
        }

        return $record;
    }

    private function isValidRecord(array $record): bool
    {
        if (empty(trim((string) ($record['product_name'] ?? '')))) {
            return false;
        }

        if (empty(trim((string) ($record['category'] ?? '')))) {
            return false;
        }

        if (empty(trim((string) ($record['unit_of_measure'] ?? '')))) {
            return false;
        }

        return is_numeric($record['price'] ?? null) && (float) $record['price'] > 0;
    }

    private function generateProductCode(string $name): string
    {
        return Str::upper(Str::slug($name, '_')) . '_' . Str::upper(Str::random(4));
    }
}
