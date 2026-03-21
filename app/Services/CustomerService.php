<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerService
{
    public function listForTenant(string $tenantId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->scopedQuery($tenantId)
            ->orderByDesc('created_at')
            ->paginate(max(1, min($perPage, 100)));
    }

    public function findForTenant(string $tenantId, string $identifier): ?Customer
    {
        $query = $this->scopedQuery($tenantId);

        if (ctype_digit($identifier)) {
            return $query->whereKey((int) $identifier)->first();
        }

        return $query->where('customer_code', $identifier)->first();
    }

    public function createForTenant(string $tenantId, array $payload): Customer
    {
        $data = [
            'tenant_id' => $this->hasColumn('tenant_id') ? $tenantId : null,
            'customer_code' => $payload['customer_code'] ?? ('CUST-' . Str::upper(Str::random(8))),
            'status' => $payload['status'] ?? 'active',
        ];

        if ($this->hasColumn('personal_info')) {
            $data['personal_info'] = [
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
            ];
            $data['address'] = $payload['address'] ?? null;
            $data['preferences'] = $payload['preferences'] ?? [];
            $data['loyalty'] = $payload['loyalty'] ?? ['points_balance' => 0, 'tier' => 'bronze'];
            $data['purchasing_history'] = $payload['purchasing_history'] ?? [];
        } else {
            $data['first_name'] = $payload['first_name'];
            $data['last_name'] = $payload['last_name'];
            $data['email'] = $payload['email'] ?? null;
            $data['phone'] = $payload['phone'] ?? null;
            $data['address'] = $payload['address'] ?? null;
            $data['preferences'] = $payload['preferences'] ?? [];
        }

        return Customer::create($this->compactNulls($data));
    }

    public function update(Customer $customer, array $payload): Customer
    {
        $data = ['status' => $payload['status'] ?? $customer->status];

        if ($this->hasColumn('personal_info')) {
            $personal = $customer->personal_info ?? [];
            $personal['first_name'] = $payload['first_name'] ?? ($personal['first_name'] ?? null);
            $personal['last_name'] = $payload['last_name'] ?? ($personal['last_name'] ?? null);
            $personal['email'] = $payload['email'] ?? ($personal['email'] ?? null);
            $personal['phone'] = $payload['phone'] ?? ($personal['phone'] ?? null);

            $data['personal_info'] = $personal;

            if (array_key_exists('address', $payload)) {
                $data['address'] = $payload['address'];
            }
            if (array_key_exists('preferences', $payload)) {
                $data['preferences'] = $payload['preferences'];
            }
        } else {
            foreach (['first_name', 'last_name', 'email', 'phone', 'address', 'preferences'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $data[$field] = $payload[$field];
                }
            }
        }

        $customer->fill($this->compactNulls($data));
        $customer->save();

        return $customer->refresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function purchaseHistory(Customer $customer): array
    {
        return $customer->purchasing_history ?? [];
    }

    public function analytics(Customer $customer): array
    {
        $history = $customer->purchasing_history ?? [];
        $loyalty = $customer->loyalty ?? [];

        return [
            'total_orders' => (int) ($history['total_orders'] ?? 0),
            'total_spent' => (float) ($history['total_spent'] ?? 0),
            'average_order_value' => (float) ($history['average_order_value'] ?? 0),
            'loyalty_tier' => $loyalty['tier'] ?? 'bronze',
            'points_balance' => (int) ($loyalty['points_balance'] ?? 0),
        ];
    }

    public function addLoyaltyPoints(Customer $customer, int $points): Customer
    {
        if (!$this->hasColumn('loyalty')) {
            return $customer;
        }

        $loyalty = $customer->loyalty ?? ['points_balance' => 0, 'tier' => 'bronze'];
        $loyalty['points_balance'] = max(0, (int) ($loyalty['points_balance'] ?? 0) + max(0, $points));

        $customer->loyalty = $loyalty;
        $customer->save();

        return $customer->refresh();
    }

    public function redeemLoyaltyPoints(Customer $customer, int $points): Customer
    {
        if (!$this->hasColumn('loyalty')) {
            return $customer;
        }

        $loyalty = $customer->loyalty ?? ['points_balance' => 0, 'tier' => 'bronze'];
        $balance = (int) ($loyalty['points_balance'] ?? 0);
        $loyalty['points_balance'] = max(0, $balance - max(0, $points));

        $customer->loyalty = $loyalty;
        $customer->save();

        return $customer->refresh();
    }

    private function scopedQuery(string $tenantId): Builder
    {
        $query = Customer::query();

        if ($this->hasColumn('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('customers', $column);
    }

    private function compactNulls(array $data): array
    {
        return array_filter($data, static fn ($value) => $value !== null);
    }
}