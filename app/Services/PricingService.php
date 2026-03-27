<?php

namespace App\Services;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\ProductPrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PricingService
{
    public function resolveCurrentPrice(
        string $tenantId,
        int $productId,
        string $channel = 'retail',
        ?float $quantity = null,
        ?Carbon $asOf = null
    ): ?array {
        $asOf = $asOf ?? now();

        $listQuery = PriceList::query()
            ->where('channel', $channel)
            ->where('status', 'published')
            ->where('effective_from', '<=', $asOf)
            ->where(function ($query) use ($asOf) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', $asOf);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id');

        if ($this->hasColumn('price_lists', 'tenant_id')) {
            $listQuery->where('tenant_id', $tenantId);
        }

        $activeList = $listQuery->first();
        if (!$activeList) {
            return null;
        }

        $item = null;

        if (Schema::hasTable('product_prices')) {
            $productPriceQuery = ProductPrice::query()
                ->where('price_list_id', $activeList->id)
                ->where('product_id', $productId)
                ->where('uom', 'kg')
                ->orderByDesc('id');

            if ($this->hasColumn('product_prices', 'tenant_id')) {
                $productPriceQuery->where('tenant_id', $tenantId);
            }

            $item = $productPriceQuery->first();
        }

        if (!$item && Schema::hasTable('price_list_items')) {
            $legacyQuery = PriceListItem::query()
                ->where('price_list_id', $activeList->id)
                ->where('product_id', $productId)
                ->orderByDesc('min_qty');

            if ($this->hasColumn('price_list_items', 'tenant_id')) {
                $legacyQuery->where('tenant_id', $tenantId);
            }

            if ($quantity !== null) {
                $legacyQuery->where(function ($query) use ($quantity) {
                    $query->whereNull('min_qty')->orWhere('min_qty', '<=', $quantity);
                })->where(function ($query) use ($quantity) {
                    $query->whereNull('max_qty')->orWhere('max_qty', '>=', $quantity);
                });
            }

            $item = $legacyQuery->first();
        }
        if (!$item) {
            return null;
        }

        return [
            'price' => (float) $item->price,
            'currency' => $activeList->currency,
            'price_list_id' => (int) $activeList->id,
            'price_list_code' => $activeList->code,
            'price_list_name' => $activeList->name,
            'effective_from' => $activeList->effective_from,
            'effective_to' => $activeList->effective_to,
        ];
    }

    public function getCurrentPosPrice(string $tenantId, int $productId): ?float
    {
        $resolved = $this->resolveCurrentPrice($tenantId, $productId, 'retail', 1.0);

        return $resolved ? (float) $resolved['price'] : null;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }
}
