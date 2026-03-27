@extends('layouts.tenant')

@section('title', 'Products - Meat Shop POS')
@section('page_title', 'Products')
@section('page_subtitle', 'Manage meat cuts, byproducts, and pricing across your branch')

@php
    $canExportCsv = \App\Services\SubscriptionService::hasFeature('export_csv')
        || \App\Services\SubscriptionService::hasFeature('data_export');

    $currentProductCount = (int) data_get($productStats ?? [], 'total_products', 0);

    $maxProducts = \App\Services\SubscriptionService::getPlanLimits(\App\Services\SubscriptionService::resolveCurrentPlan())['max_products'] ?? null;
    $isUnlimitedProducts = $maxProducts === null || (int) $maxProducts < 0;
    $canAddProduct = $isUnlimitedProducts || \App\Services\SubscriptionService::isWithinLimit('max_products', $currentProductCount + 1);
@endphp

@section('header_actions')
    @if($canExportCsv)
        <button type="button" onclick="exportProducts('csv')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="download" class="h-4 w-4"></i>
            Export CSV
        </button>
    @else
        <button type="button" disabled title="Export requires Standard plan or higher." class="inline-flex cursor-not-allowed items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
            <i data-lucide="download" class="h-4 w-4"></i>
            Export CSV
        </button>
    @endif

    @if($canAddProduct)
        <button type="button" onclick="showAddProductModal()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Product
        </button>
    @else
        <button type="button" disabled title="Product limit reached. Upgrade to add more products." class="inline-flex cursor-not-allowed items-center gap-2 rounded-full bg-slate-300 px-4 py-2 text-sm font-semibold text-white">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Product
        </button>
    @endif
@endsection

@section('content')
<section class="space-y-6">
    @if(!$isUnlimitedProducts)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Product cap: <span class="font-semibold">{{ (int) $maxProducts }}</span>. Current count: <span class="font-semibold">{{ $currentProductCount }}</span>.
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format((int) data_get($productStats ?? [], 'total_products', 0)) }}</p>
            <p class="mt-1 text-xs text-slate-500">Current tenant catalog size</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Low Stock</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format((int) data_get($productStats ?? [], 'low_stock_products', 0)) }}</p>
            <p class="mt-1 text-xs text-slate-500">Below reorder threshold</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Avg Margin</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ data_get($productStats ?? [], 'average_margin') !== null ? number_format((float) data_get($productStats, 'average_margin', 0), 1) . '%' : 'N/A' }}</p>
            <p class="mt-1 text-xs text-slate-500">Based on available product pricing</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catalog Health</p>
            @php
                $outOfStock = (int) data_get($productStats ?? [], 'out_of_stock_products', 0);
                $catalogHealth = $outOfStock === 0 ? 'Good' : ($outOfStock <= 3 ? 'Watchlist' : 'At Risk');
            @endphp
            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $catalogHealth }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ number_format($outOfStock) }} out-of-stock item(s)</p>
        </article>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="heading-font text-lg font-semibold text-slate-900">Catalog</h2>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input id="productSearch" type="text" placeholder="Search products..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-0 transition focus:border-indigo-300 focus:shadow-sm sm:w-56">
                <select id="stockFilter" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-indigo-300">
                    <option value="">All stock levels</option>
                    <option value="instock">In stock</option>
                    <option value="lowstock">Low stock</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">Product</th>
                        <th class="px-3 py-3">Category</th>
                        <th class="px-3 py-3">Price / kg</th>
                        <th class="px-3 py-3">Stock</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTable" class="divide-y divide-slate-100 text-slate-700">
                    @forelse(($pageProducts ?? collect()) as $product)
                        @php
                            $stock = (float) data_get($product->inventory, 'current_stock', 0);
                            $reorderLevel = (float) data_get($product->inventory, 'reorder_level', 0);
                            $price = (float) data_get($product->pricing, 'price_per_unit', 0);
                            $statusLabel = $stock <= 0
                                ? 'Out of Stock'
                                : ($stock <= $reorderLevel ? 'Low Stock' : 'In Stock');
                            $statusClass = $stock <= 0
                                ? 'bg-rose-100 text-rose-700'
                                : ($stock <= $reorderLevel ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                        @endphp
                        <tr>
                            <td class="px-3 py-3 font-medium">{{ $product->name }}</td>
                            <td class="px-3 py-3">{{ ucfirst((string) ($product->category ?? 'Uncategorized')) }}</td>
                            <td class="px-3 py-3 font-semibold">PHP {{ number_format($price, 2) }}</td>
                            <td class="px-3 py-3">{{ number_format($stock, 2) }} {{ data_get($product->inventory, 'unit_of_measure', 'kg') }}</td>
                            <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="px-3 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <button onclick="editProduct('{{ addslashes((string) $product->name) }}')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                    <button onclick="deleteProduct('{{ addslashes((string) $product->name) }}')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500">No products found for this tenant yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection

@push('scripts')
<script>
function toast(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            timer: 2400,
            showConfirmButton: false,
            icon,
            title: message,
        });
        return;
    }

    alert(message);
}

function showAddProductModal() {
    toast('Product form will open here.', 'info');
}

function editProduct(name) {
    toast('Edit product: ' + name, 'info');
}

function deleteProduct(name) {
    toast('Delete product: ' + name, 'warning');
}

function exportProducts(type) {
    toast('Exporting products as ' + type.toUpperCase(), 'success');
}

document.getElementById('productSearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#productsTable tr').forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});

document.getElementById('stockFilter')?.addEventListener('change', function () {
    const filter = this.value;
    document.querySelectorAll('#productsTable tr').forEach((row) => {
        const text = row.innerText.toLowerCase();
        if (!filter) {
            row.style.display = '';
            return;
        }

        const visible =
            (filter === 'instock' && text.includes('in stock')) ||
            (filter === 'lowstock' && text.includes('low stock'));
        row.style.display = visible ? '' : 'none';
    });
});
</script>
@endpush