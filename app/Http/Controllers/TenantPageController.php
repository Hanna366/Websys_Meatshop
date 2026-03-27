<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantPageController extends Controller
{
    private ?string $resolvedConnection = null;

    public function dashboard(Request $request)
    {
        $tenant = $this->resolveTenant($request);
        $tenantId = $tenant?->tenant_id;

        $salesQuery = $this->tenantScopedSaleQuery($tenantId);
        $productQuery = $this->tenantScopedProductQuery($tenantId);
        $customerQuery = $this->tenantScopedCustomerQuery($tenantId);

        $todaySales = $salesQuery
            ? (float) (clone $salesQuery)
                ->where('status', 'completed')
                ->whereDate('created_at', now()->toDateString())
                ->sum('grand_total')
            : 0.0;

        $products = $productQuery ? (clone $productQuery)->get() : collect();
        $customersCount = $customerQuery ? (clone $customerQuery)->count() : 0;
        $lowStockItems = $products->filter(fn (Product $product) => $product->isLowStock())->count();

        $recentSalesRows = $salesQuery
            ? (clone $salesQuery)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
            : collect();

        $customerNames = $this->customerNamesForSales($tenantId, $recentSalesRows);

        $recentSales = $recentSalesRows->map(function (Sale $sale) use ($customerNames) {
            $items = collect($sale->items ?? []);
            $productSummary = $items->pluck('name')->filter()->take(3)->implode(', ');
            if ($productSummary === '') {
                $productSummary = $items->count() . ' item(s)';
            }

            $status = ucfirst((string) ($sale->status ?? 'pending'));

            return [
                'id' => $sale->sale_code ?: ('SAL-' . $sale->id),
                'customer' => $customerNames[(string) $sale->customer_id] ?? 'Walk-in Customer',
                'products' => $productSummary,
                'total' => (float) ($sale->grand_total ?? $sale->total ?? 0),
                'status' => $status,
                'date' => optional($sale->created_at)->format('Y-m-d H:i'),
            ];
        });

        return view('dashboard.index', [
            'tenant' => $tenant,
            'dashboardStats' => [
                'today_sales' => $todaySales,
                'products' => $products->count(),
                'low_stock_items' => $lowStockItems,
                'customers' => $customersCount,
            ],
            'recentSales' => $recentSales,
        ]);
    }

    public function products(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        $productQuery = $this->tenantScopedProductQuery($tenantId);
        $products = $productQuery ? $productQuery->orderByDesc('created_at')->limit(100)->get() : collect();

        $lowStockCount = $products->filter(fn (Product $product) => $product->isLowStock())->count();
        $averageMargin = $products
            ->map(function (Product $product) {
                $price = (float) data_get($product->pricing, 'price_per_unit', 0);
                $cost = (float) data_get($product->pricing, 'cost_per_unit', 0);
                if ($price <= 0 || $cost <= 0 || $cost >= $price) {
                    return null;
                }

                return (($price - $cost) / $price) * 100;
            })
            ->filter(static fn ($margin) => $margin !== null)
            ->avg();

        $outOfStockCount = $products->filter(fn (Product $product) => $product->getCurrentStock() <= 0)->count();

        return view('products', [
            'pageProducts' => $products,
            'productStats' => [
                'total_products' => $products->count(),
                'low_stock_products' => $lowStockCount,
                'average_margin' => $averageMargin,
                'out_of_stock_products' => $outOfStockCount,
            ],
        ]);
    }

    public function inventory(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        $productQuery = $this->tenantScopedProductQuery($tenantId);
        $products = $productQuery ? $productQuery->orderByDesc('updated_at')->limit(100)->get() : collect();

        $lowStockCount = $products->filter(fn (Product $product) => $product->isLowStock())->count();
        $healthyStockCount = $products->count() - $lowStockCount;
        $totalValue = $products->sum(function (Product $product) {
            $stock = $product->getCurrentStock();
            $pricePerUnit = (float) data_get($product->pricing, 'price_per_unit', 0);
            return $stock * max(0, $pricePerUnit);
        });

        return view('inventory', [
            'inventoryProducts' => $products,
            'inventoryStats' => [
                'total_products' => $products->count(),
                'low_stock_products' => $lowStockCount,
                'healthy_stock_products' => max(0, $healthyStockCount),
                'total_value' => (float) $totalValue,
            ],
        ]);
    }

    public function sales(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        $salesQuery = $this->tenantScopedSaleQuery($tenantId);

        $todayStart = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $todayRevenue = $salesQuery
            ? (float) (clone $salesQuery)
                ->where('status', 'completed')
                ->where('created_at', '>=', $todayStart)
                ->sum('grand_total')
            : 0.0;

        $weekRevenue = $salesQuery
            ? (float) (clone $salesQuery)
                ->where('status', 'completed')
                ->where('created_at', '>=', $weekStart)
                ->sum('grand_total')
            : 0.0;

        $monthRevenue = $salesQuery
            ? (float) (clone $salesQuery)
                ->where('status', 'completed')
                ->where('created_at', '>=', $monthStart)
                ->sum('grand_total')
            : 0.0;

        $monthTransactions = $salesQuery
            ? (int) (clone $salesQuery)
                ->where('status', 'completed')
                ->where('created_at', '>=', $monthStart)
                ->count()
            : 0;

        $recentSalesRows = $salesQuery
            ? (clone $salesQuery)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
            : collect();

        $customerNames = $this->customerNamesForSales($tenantId, $recentSalesRows);

        return view('sales', [
            'salesRows' => $recentSalesRows,
            'salesCustomerNames' => $customerNames,
            'salesStats' => [
                'today_revenue' => $todayRevenue,
                'week_revenue' => $weekRevenue,
                'month_revenue' => $monthRevenue,
                'month_transactions' => $monthTransactions,
                'average_order' => $monthTransactions > 0 ? ($monthRevenue / $monthTransactions) : 0,
            ],
        ]);
    }

    public function customers(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        $customerQuery = $this->tenantScopedCustomerQuery($tenantId);
        $customers = $customerQuery ? $customerQuery->orderByDesc('created_at')->limit(100)->get() : collect();

        $salesQuery = $this->tenantScopedSaleQuery($tenantId);
        $activeThisMonth = $salesQuery
            ? (int) (clone $salesQuery)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->whereNotNull('customer_id')
                ->distinct('customer_id')
                ->count('customer_id')
            : 0;

        $newThisWeek = $customerQuery
            ? (int) (clone $customerQuery)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count()
            : 0;

        $vipCustomers = $customers->filter(function (Customer $customer) {
            $tier = strtolower((string) data_get($customer->loyalty, 'tier', ''));
            $spent = (float) data_get($customer->purchasing_history, 'total_spent', 0);
            return in_array($tier, ['vip', 'gold', 'platinum'], true) || $spent >= 50000;
        })->count();

        return view('customers', [
            'customerRows' => $customers,
            'customerStats' => [
                'total' => $customers->count(),
                'active_this_month' => $activeThisMonth,
                'new_this_week' => $newThisWeek,
                'vip' => $vipCustomers,
            ],
        ]);
    }

    public function suppliers(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        $supplierQuery = $this->tenantScopedSupplierQuery($tenantId);
        $suppliers = $supplierQuery ? $supplierQuery->orderByDesc('created_at')->limit(100)->get() : collect();

        $active = $suppliers->filter(fn (Supplier $supplier) => strtolower((string) $supplier->status) === 'active')->count();
        $pending = $suppliers->filter(fn (Supplier $supplier) => strtolower((string) $supplier->status) === 'pending')->count();

        $deliveriesToday = $suppliers->filter(function (Supplier $supplier) {
            $deliveryDate = data_get($supplier->delivery_info, 'next_delivery_date')
                ?? data_get($supplier->details, 'next_delivery_date');

            if (!$deliveryDate) {
                return false;
            }

            return (string) $deliveryDate === now()->toDateString();
        })->count();

        return view('suppliers', [
            'supplierRows' => $suppliers,
            'supplierStats' => [
                'total' => $suppliers->count(),
                'active' => $active,
                'pending' => $pending,
                'deliveries_today' => $deliveriesToday,
            ],
        ]);
    }

    public function reports(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        $salesBaseQuery = $this->tenantScopedSaleQuery($tenantId);
        $salesQuery = $salesBaseQuery ? (clone $salesBaseQuery)->where('status', 'completed') : null;

        $totalRevenue = $salesQuery ? (float) (clone $salesQuery)->sum('grand_total') : 0.0;
        $totalSales = $salesQuery ? (int) (clone $salesQuery)->count() : 0;
        $averageSale = $totalSales > 0 ? ($totalRevenue / $totalSales) : 0;

        $topProduct = $salesQuery
            ? (clone $salesQuery)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->flatMap(fn (Sale $sale) => collect($sale->items ?? []))
                ->groupBy(fn ($item) => (string) ($item['name'] ?? 'Unknown'))
                ->sortByDesc(fn (Collection $items) => $items->sum(fn ($item) => (float) ($item['line_total'] ?? 0)))
                ->keys()
                ->first()
            : null;

        $daily = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $daily[] = [
                'label' => $day->format('D'),
                'value' => $salesQuery
                    ? (float) (clone $salesQuery)
                        ->whereDate('created_at', $day->toDateString())
                        ->sum('grand_total')
                    : 0.0,
            ];
        }

        $productQuery = $this->tenantScopedProductQuery($tenantId);
        $categories = $productQuery
            ? $productQuery
                ->selectRaw('COALESCE(category, "Uncategorized") as category_name, COUNT(*) as count')
                ->groupBy('category_name')
                ->orderByDesc('count')
                ->limit(8)
                ->get()
                ->map(fn ($row) => ['label' => (string) $row->category_name, 'value' => (int) $row->count])
                ->values()
            : collect();

        $detailedSales = $salesQuery
            ? (clone $salesQuery)
                ->orderByDesc('created_at')
                ->limit(25)
                ->get()
            : collect();

        $customerNames = $this->customerNamesForSales($tenantId, $detailedSales);

        return view('reports', [
            'reportStats' => [
                'total_revenue' => $totalRevenue,
                'total_sales' => $totalSales,
                'average_sale' => $averageSale,
                'top_product' => $topProduct ?: 'No sales yet',
            ],
            'salesTrend' => $daily,
            'categorySplit' => $categories,
            'detailedSales' => $detailedSales,
            'salesCustomerNames' => $customerNames,
        ]);
    }

    private function resolveTenantId(Request $request): string
    {
        if (app()->bound('tenant') && tenant()) {
            return (string) tenant()->tenant_id;
        }

        $sessionTenantId = (string) session('user.tenant_id', '');
        if ($sessionTenantId !== '') {
            return $sessionTenantId;
        }

        $host = strtolower((string) $request->getHost());
        $tenant = Tenant::where('domain', $host)->first();

        return (string) ($tenant?->tenant_id ?? '');
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        if (app()->bound('tenant') && tenant()) {
            return tenant();
        }

        $tenantId = $this->resolveTenantId($request);
        if ($tenantId !== '') {
            return Tenant::where('tenant_id', $tenantId)->first();
        }

        return null;
    }

    private function tenantScopedProductQuery(?string $tenantId)
    {
        $connection = $this->resolveTenantConnection($tenantId);

        if (!$this->hasTable('products', $connection)) {
            return null;
        }

        $query = Product::on($connection)->newQuery();

        if ($tenantId && $this->tableHasColumn('products', 'tenant_id', $connection)) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function tenantScopedSaleQuery(?string $tenantId)
    {
        $connection = $this->resolveTenantConnection($tenantId);

        if (!$this->hasTable('sales', $connection)) {
            return null;
        }

        $query = Sale::on($connection)->newQuery();

        if ($tenantId && $this->tableHasColumn('sales', 'tenant_id', $connection)) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function tenantScopedCustomerQuery(?string $tenantId)
    {
        $connection = $this->resolveTenantConnection($tenantId);

        if (!$this->hasTable('customers', $connection)) {
            return null;
        }

        $query = Customer::on($connection)->newQuery();

        if ($tenantId && $this->tableHasColumn('customers', 'tenant_id', $connection)) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function tenantScopedSupplierQuery(?string $tenantId)
    {
        $connection = $this->resolveTenantConnection($tenantId);

        if (!$this->hasTable('suppliers', $connection)) {
            return null;
        }

        $query = Supplier::on($connection)->newQuery();

        if ($tenantId && $this->tableHasColumn('suppliers', 'tenant_id', $connection)) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    private function customerNamesForSales(?string $tenantId, Collection $sales): array
    {
        $ids = $sales->pluck('customer_id')->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $customerQuery = $this->tenantScopedCustomerQuery($tenantId);
        if (!$customerQuery) {
            return [];
        }

        $customerQuery->whereIn('id', $ids->all());

        return $customerQuery->get()->mapWithKeys(function (Customer $customer) {
            $fullName = trim(implode(' ', array_filter([
                (string) ($customer->first_name ?? data_get($customer->personal_info, 'first_name', '')),
                (string) ($customer->last_name ?? data_get($customer->personal_info, 'last_name', '')),
            ])));

            return [(string) $customer->id => ($fullName !== '' ? $fullName : 'Customer #' . $customer->id)];
        })->toArray();
    }

    private function hasTable(string $table, string $connection): bool
    {
        static $tableCache = [];

        $key = $connection . ':' . $table;

        if (!array_key_exists($key, $tableCache)) {
            $tableCache[$key] = Schema::connection($connection)->hasTable($table);
        }

        return $tableCache[$key];
    }

    private function tableHasColumn(string $table, string $column, string $connection): bool
    {
        static $columnCache = [];

        $key = $connection . ':' . $table . ':' . $column;
        if (!array_key_exists($key, $columnCache)) {
            $columnCache[$key] = $this->hasTable($table, $connection) && Schema::connection($connection)->hasColumn($table, $column);
        }

        return $columnCache[$key];
    }

    private function resolveTenantConnection(?string $tenantId): string
    {
        if ($this->resolvedConnection !== null) {
            return $this->resolvedConnection;
        }

        if (app()->bound('tenant') && tenant()) {
            $this->resolvedConnection = 'tenant';
            return $this->resolvedConnection;
        }

        if (!empty($tenantId)) {
            $tenantModel = Tenant::query()->where('tenant_id', $tenantId)->first();
            if ($tenantModel && method_exists($tenantModel, 'getTenantDatabaseConfig')) {
                config(['database.connections.tenant' => $tenantModel->getTenantDatabaseConfig()]);
                DB::purge('tenant');
                $this->resolvedConnection = 'tenant';
                return $this->resolvedConnection;
            }
        }

        $this->resolvedConnection = (string) config('database.default', 'mysql');
        return $this->resolvedConnection;
    }
}