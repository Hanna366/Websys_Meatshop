<?php

namespace App\Services;

use App\Models\PriceList;
use App\Models\PriceListItem;
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

        $itemQuery = PriceListItem::query()
            ->where('price_list_id', $activeList->id)
            ->where('product_id', $productId)
            ->orderByDesc('min_qty');

        if ($this->hasColumn('price_list_items', 'tenant_id')) {
            $itemQuery->where('tenant_id', $tenantId);
        }

        if ($quantity !== null) {
            $itemQuery->where(function ($query) use ($quantity) {
                $query->whereNull('min_qty')->orWhere('min_qty', '<=', $quantity);
            })->where(function ($query) use ($quantity) {
                $query->whereNull('max_qty')->orWhere('max_qty', '>=', $quantity);
            });
        }

        $item = $itemQuery->first();
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

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }
}
