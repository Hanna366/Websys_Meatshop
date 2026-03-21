<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryService
{
    public function listInventory(string $tenantId)
    {
        return app(ProductService::class)->listForTenant($tenantId, ['limit' => 100]);
    }

    public function stats(string $tenantId): array
    {
        $products = app(ProductService::class)->listForTenant($tenantId, ['limit' => 100])->getCollection();

        $totalProducts = $products->count();
        $activeProducts = $products->where('status', 'active')->count();
        $lowStockCount = $products->filter(fn (Product $product) => $product->isLowStock())->count();

        $currentStock = $products->sum(fn (Product $product) => $product->getCurrentStock());

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'low_stock_products' => $lowStockCount,
            'total_current_stock' => $currentStock,
        ];
    }

    public function alerts(string $tenantId): array
    {
        $lowStock = app(ProductService::class)->lowStock($tenantId);

        $expiringBatchesQuery = InventoryBatch::query()
            ->whereDate('expiry_date', '<=', now()->addDays(7))
            ->whereDate('expiry_date', '>=', now()->startOfDay())
            ->orderBy('expiry_date');

        if (Schema::hasColumn('products', 'tenant_id')) {
            $productIds = Product::query()
                ->where('tenant_id', $tenantId)
                ->pluck('id');

            $expiringBatchesQuery->whereIn('product_id', $productIds);
        }

        $expiringBatches = $expiringBatchesQuery
            ->limit(100)
            ->get();

        return [
            'low_stock' => $lowStock,
            'expiring_batches' => $expiringBatches,
        ];
    }

    public function productBatches(Product $product)
    {
        return InventoryBatch::query()
            ->where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findBatchByIdentifier(string $tenantId, string $identifier): ?InventoryBatch
    {
        $query = InventoryBatch::query();

        if (ctype_digit($identifier)) {
            $query->whereKey((int) $identifier);
        } else {
            $query->where('batch_code', $identifier);
        }

        $batch = $query->first();
        if (!$batch) {
            return null;
        }

        $product = $batch->product;
        if (!$product) {
            return null;
        }

        if (Schema::hasColumn('products', 'tenant_id') && (string) $product->tenant_id !== $tenantId) {
            return null;
        }

        return $batch;
    }

    public function addBatch(Product $product, array $payload): InventoryBatch
    {
        return DB::transaction(function () use ($product, $payload) {
            $quantity = (float) ($payload['quantity'] ?? 0);

            $batch = InventoryBatch::create([
                'product_id' => $product->id,
                'batch_code' => $payload['batch_code'] ?? ('BAT-' . now()->format('YmdHis') . '-' . $product->id),
                'quantity' => $quantity,
                'expiry_date' => $payload['expiry_date'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            $inventory = $product->inventory ?? [];
            $inventory['current_stock'] = ((float) ($inventory['current_stock'] ?? 0)) + $quantity;
            $product->inventory = $inventory;
            $product->save();

            return $batch;
        });
    }

    public function updateBatch(InventoryBatch $batch, array $payload): InventoryBatch
    {
        return DB::transaction(function () use ($batch, $payload) {
            $previousQuantity = (float) $batch->quantity;

            $batch->fill([
                'batch_code' => $payload['batch_code'] ?? $batch->batch_code,
                'quantity' => $payload['quantity'] ?? $batch->quantity,
                'expiry_date' => $payload['expiry_date'] ?? $batch->expiry_date,
                'metadata' => $payload['metadata'] ?? $batch->metadata,
            ]);
            $batch->save();

            $quantityDelta = ((float) $batch->quantity) - $previousQuantity;
            if ($quantityDelta !== 0.0) {
                $product = $batch->product;
                if ($product) {
                    $inventory = $product->inventory ?? [];
                    $inventory['current_stock'] = ((float) ($inventory['current_stock'] ?? 0)) + $quantityDelta;
                    $product->inventory = $inventory;
                    $product->save();
                }
            }

            return $batch->refresh();
        });
    }

    public function recordWaste(InventoryBatch $batch, float $quantity, ?string $reason = null): InventoryBatch
    {
        return DB::transaction(function () use ($batch, $quantity, $reason) {
            $quantity = max(0.0, $quantity);
            $newQuantity = max(0.0, ((float) $batch->quantity) - $quantity);

            $metadata = $batch->metadata ?? [];
            $metadata['waste_log'][] = [
                'quantity' => $quantity,
                'reason' => $reason,
                'recorded_at' => now()->toIso8601String(),
            ];

            $batch->quantity = $newQuantity;
            $batch->metadata = $metadata;
            $batch->save();

            $product = $batch->product;
            if ($product) {
                $inventory = $product->inventory ?? [];
                $inventory['current_stock'] = max(0.0, ((float) ($inventory['current_stock'] ?? 0)) - $quantity);
                $product->inventory = $inventory;
                $product->save();
            }

            return $batch->refresh();
        });
    }
}