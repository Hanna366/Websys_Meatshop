<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SalesService
{
    public function listForTenant(string $tenantId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->scopedQuery($tenantId)
            ->orderByDesc('created_at')
            ->paginate(max(1, min($perPage, 100)));
    }

    public function findForTenant(string $tenantId, string $identifier): ?Sale
    {
        $query = $this->scopedQuery($tenantId);

        if (ctype_digit($identifier)) {
            return $query->whereKey((int) $identifier)->first();
        }

        return $query->where('sale_code', $identifier)->first();
    }

    public function process(string $tenantId, int $userId, array $payload): Sale
    {
        return DB::transaction(function () use ($tenantId, $userId, $payload) {
            $items = $payload['items'];
            $saleItems = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $productId = (string) ($item['product_id'] ?? '');
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                $product = app(ProductService::class)->findForTenant($tenantId, $productId);
                if (!$product) {
                    throw new \RuntimeException("Product {$productId} not found in tenant scope.");
                }

                $inventory = $product->inventory ?? [];
                $currentStock = (float) ($inventory['current_stock'] ?? 0);
                if ($quantity <= 0 || $currentStock < $quantity) {
                    throw new \RuntimeException("Insufficient stock for product {$product->id}.");
                }

                if ($unitPrice <= 0) {
                    $unitPrice = (float) ($product->pricing['price_per_unit'] ?? 0);
                }

                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                $inventory['current_stock'] = $currentStock - $quantity;
                $product->inventory = $inventory;
                $product->save();

                $saleItems[] = [
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $tax = (float) ($payload['tax'] ?? 0);
            $discount = (float) ($payload['discount'] ?? 0);
            $grandTotal = max(0.0, $subtotal + $tax - $discount);

            $saleData = [
                'tenant_id' => $this->hasSaleColumn('tenant_id') ? $tenantId : null,
                'sale_code' => $payload['sale_code'] ?? ('SAL-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4))),
                'customer_id' => $payload['customer_id'] ?? null,
                'user_id' => $userId,
                'items' => $saleItems,
                'total' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'status' => 'completed',
            ];

            return Sale::create($this->compactNulls($saleData));
        });
    }

    public function void(Sale $sale): Sale
    {
        if ($sale->status === 'voided') {
            return $sale;
        }

        return DB::transaction(function () use ($sale) {
            $items = $sale->items ?? [];
            $saleTenantId = (string) ($sale->tenant_id ?? '');
            $hasProductTenantColumn = Schema::hasColumn('products', 'tenant_id');

            foreach ($items as $item) {
                $productQuery = Product::query()->whereKey($item['product_id'] ?? null);
                if ($hasProductTenantColumn && $saleTenantId !== '') {
                    $productQuery->where('tenant_id', $saleTenantId);
                }

                $product = $productQuery->first();
                if (!$product) {
                    continue;
                }

                $inventory = $product->inventory ?? [];
                $inventory['current_stock'] = ((float) ($inventory['current_stock'] ?? 0)) + (float) ($item['quantity'] ?? 0);
                $product->inventory = $inventory;
                $product->save();
            }

            $sale->status = 'voided';
            $sale->save();

            return $sale->refresh();
        });
    }

    public function summary(string $tenantId): array
    {
        $query = $this->scopedQuery($tenantId)->where('status', 'completed');

        return [
            'total_sales' => (int) $query->count(),
            'gross_revenue' => (float) $query->sum('total'),
            'tax_collected' => (float) $query->sum('tax'),
            'discount_total' => (float) $query->sum('discount'),
            'net_revenue' => (float) $query->sum('grand_total'),
        ];
    }

    public function dailyReport(string $tenantId, int $days = 7): array
    {
        $start = now()->subDays(max(1, $days) - 1)->startOfDay();

        $rows = $this->scopedQuery($tenantId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as orders_count, SUM(grand_total) as gross_total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        return $rows->map(function ($row) {
            return [
                'date' => $row->sale_date,
                'orders_count' => (int) $row->orders_count,
                'gross_total' => (float) $row->gross_total,
            ];
        })->toArray();
    }

    private function scopedQuery(string $tenantId): Builder
    {
        $query = Sale::query();

        if ($this->hasSaleColumn('tenant_id')) {
            return $query->where('tenant_id', $tenantId);
        }

        if ($this->hasSaleColumn('user_id') && Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            $userIds = User::query()->where('tenant_id', $tenantId)->pluck('id');
            return $query->whereIn('user_id', $userIds);
        }

        return $query;
    }

    private function hasSaleColumn(string $column): bool
    {
        return Schema::hasColumn('sales', $column);
    }

    private function compactNulls(array $data): array
    {
        return array_filter($data, static fn ($value) => $value !== null);
    }
}