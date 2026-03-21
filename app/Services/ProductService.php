<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductService
{
    public function listForTenant(string $tenantId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->scopedQuery($tenantId);

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($filters['limit'] ?? 25);
        $perPage = max(1, min($perPage, 100));

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function findForTenant(string $tenantId, string $identifier): ?Product
    {
        $query = $this->scopedQuery($tenantId);

        if (ctype_digit($identifier)) {
            return $query->whereKey((int) $identifier)->first();
        }

        return $query->where('product_code', $identifier)->first();
    }

    public function createForTenant(string $tenantId, int $userId, array $payload): Product
    {
        $productCode = $payload['product_code'] ?? $this->generateProductCode($payload['name'] ?? 'product');

        return Product::create([
            'tenant_id' => $this->hasTenantColumn('products') ? $tenantId : null,
            'product_code' => $productCode,
            'name' => $payload['name'],
            'description' => $payload['description'] ?? null,
            'category' => $payload['category'],
            'subcategory' => $payload['subcategory'] ?? null,
            'pricing' => $payload['pricing'] ?? [
                'price_per_unit' => 0,
                'unit_type' => 'kg',
            ],
            'inventory' => $payload['inventory'] ?? [
                'current_stock' => 0,
                'reorder_level' => 0,
                'unit_of_measure' => 'kg',
            ],
            'batch_tracking' => $payload['batch_tracking'] ?? [
                'enabled' => true,
            ],
            'physical_attributes' => $payload['physical_attributes'] ?? null,
            'supplier_info' => $payload['supplier_info'] ?? null,
            'images' => $payload['images'] ?? null,
            'tags' => $payload['tags'] ?? null,
            'barcode' => $payload['barcode'] ?? null,
            'status' => $payload['status'] ?? 'active',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(Product $product, int $userId, array $payload): Product
    {
        $product->fill($payload);
        $product->updated_by = $userId;
        $product->save();

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function categories(string $tenantId): array
    {
        return $this->scopedQuery($tenantId)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public function search(string $tenantId, string $term, ?string $category = null, int $limit = 25)
    {
        $query = $this->scopedQuery($tenantId)
            ->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('product_code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });

        if ($category) {
            $query->where('category', $category);
        }

        return $query->orderBy('name')->limit(max(1, min($limit, 100)))->get();
    }

    public function lowStock(string $tenantId)
    {
        return $this->scopedQuery($tenantId)
            ->get()
            ->filter(fn (Product $product) => $product->isLowStock())
            ->values();
    }

    private function scopedQuery(string $tenantId): Builder
    {
        $query = Product::query();

        if ($this->hasTenantColumn('products')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function hasTenantColumn(string $table): bool
    {
        return Schema::hasColumn($table, 'tenant_id');
    }

    private function generateProductCode(string $name): string
    {
        $slug = Str::upper(Str::slug($name, '_'));
        return trim($slug . '_' . Str::upper(Str::random(4)), '_');
    }
}