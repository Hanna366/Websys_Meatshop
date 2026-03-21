<?php

namespace App\Services;

class ReportService
{
    public function dashboard(string $tenantId): array
    {
        $salesSummary = app(SalesService::class)->summary($tenantId);
        $inventoryStats = app(InventoryService::class)->stats($tenantId);

        return [
            'sales' => $salesSummary,
            'inventory' => $inventoryStats,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function sales(string $tenantId, array $filters = []): array
    {
        return [
            'summary' => app(SalesService::class)->summary($tenantId),
            'daily' => app(SalesService::class)->dailyReport($tenantId, (int) ($filters['days'] ?? 7)),
        ];
    }

    public function inventory(string $tenantId): array
    {
        return [
            'stats' => app(InventoryService::class)->stats($tenantId),
            'alerts' => app(InventoryService::class)->alerts($tenantId),
        ];
    }

    public function customers(string $tenantId, int $limit = 50): array
    {
        $customers = app(CustomerService::class)->listForTenant($tenantId, $limit);

        return [
            'customers' => $customers,
            'count' => $customers->total(),
        ];
    }

    public function suppliers(string $tenantId, int $limit = 50): array
    {
        $suppliers = app(SupplierService::class)->listForTenant($tenantId, $limit);

        return [
            'suppliers' => $suppliers,
            'rankings' => app(SupplierService::class)->rankings($tenantId),
        ];
    }

    public function exportMeta(string $tenantId, array $filters = []): array
    {
        return [
            'tenant_id' => $tenantId,
            'filters' => $filters,
            'generated_at' => now()->toIso8601String(),
            'status' => 'ready',
            'note' => 'Use queued export job + tenant-scoped storage path for production export files.',
        ];
    }
}