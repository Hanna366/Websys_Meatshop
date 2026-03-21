<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SupplierService
{
    public function listForTenant(string $tenantId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->scopedQuery($tenantId)
            ->orderByDesc('created_at')
            ->paginate(max(1, min($perPage, 100)));
    }

    public function findForTenant(string $tenantId, string $identifier): ?Supplier
    {
        $query = $this->scopedQuery($tenantId);

        if (ctype_digit($identifier)) {
            return $query->whereKey((int) $identifier)->first();
        }

        return $query->where('supplier_code', $identifier)->first();
    }

    public function createForTenant(string $tenantId, array $payload): Supplier
    {
        $data = [
            'tenant_id' => $this->hasColumn('tenant_id') ? $tenantId : null,
            'supplier_code' => $payload['supplier_code'] ?? ('SUP-' . Str::upper(Str::random(8))),
            'status' => $payload['status'] ?? 'active',
            'address' => $payload['address'] ?? null,
        ];

        if ($this->hasColumn('business_info')) {
            $data['business_info'] = [
                'name' => $payload['name'],
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
            ];
            $data['performance_metrics'] = $payload['performance_metrics'] ?? [
                'quality_score' => 0,
                'delivery_performance' => 0,
                'price_competitiveness' => 0,
                'reliability' => 0,
            ];
        } else {
            $data['name'] = $payload['name'];
            $data['email'] = $payload['email'] ?? null;
            $data['phone'] = $payload['phone'] ?? null;
            $data['details'] = $payload['details'] ?? null;
        }

        return Supplier::create($this->compactNulls($data));
    }

    public function update(Supplier $supplier, array $payload): Supplier
    {
        $data = [];

        if ($this->hasColumn('business_info')) {
            $info = $supplier->business_info ?? [];
            $info['name'] = $payload['name'] ?? ($info['name'] ?? null);
            $info['email'] = $payload['email'] ?? ($info['email'] ?? null);
            $info['phone'] = $payload['phone'] ?? ($info['phone'] ?? null);
            $data['business_info'] = $info;

            foreach (['address', 'performance_metrics', 'status'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $data[$field] = $payload[$field];
                }
            }
        } else {
            foreach (['name', 'email', 'phone', 'address', 'details', 'status'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $data[$field] = $payload[$field];
                }
            }
        }

        $supplier->fill($this->compactNulls($data));
        $supplier->save();

        return $supplier->refresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    public function updateQualityScore(Supplier $supplier, int $score): Supplier
    {
        $score = max(0, min($score, 100));

        if ($this->hasColumn('performance_metrics')) {
            $metrics = $supplier->performance_metrics ?? [];
            $metrics['quality_score'] = $score;
            $metrics['last_updated'] = now()->toIso8601String();
            $supplier->performance_metrics = $metrics;
            $supplier->save();
            return $supplier->refresh();
        }

        $details = $supplier->details ?? [];
        $details['quality_score'] = $score;
        $details['last_updated'] = now()->toIso8601String();
        $supplier->details = $details;
        $supplier->save();

        return $supplier->refresh();
    }

    public function performance(Supplier $supplier): array
    {
        if ($this->hasColumn('performance_metrics')) {
            return $supplier->performance_metrics ?? [];
        }

        $details = $supplier->details ?? [];
        return [
            'quality_score' => (int) ($details['quality_score'] ?? 0),
            'last_updated' => $details['last_updated'] ?? null,
        ];
    }

    public function rankings(string $tenantId)
    {
        return $this->scopedQuery($tenantId)
            ->get()
            ->sortByDesc(function (Supplier $supplier) {
                $performance = $this->performance($supplier);
                return (int) ($performance['quality_score'] ?? 0);
            })
            ->values();
    }

    private function scopedQuery(string $tenantId): Builder
    {
        $query = Supplier::query();

        if ($this->hasColumn('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('suppliers', $column);
    }

    private function compactNulls(array $data): array
    {
        return array_filter($data, static fn ($value) => $value !== null);
    }
}