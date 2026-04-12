@extends('layouts.tenant')

@section('title', 'Inventory - Meat Shop POS')
@section('page_title', 'Inventory')
@section('page_subtitle', 'Track stock movement, reorder points, and inventory value')

@php
    $canExportInventory = \App\Services\SubscriptionService::hasFeature('data_export')
        || \App\Services\SubscriptionService::hasFeature('export_csv');
    // Permission flags are now provided by the controller (RBAC/Spatie-aware)
    $canManageInventory = $canManageInventory ?? false;
    $canViewInventory = $canViewInventory ?? true; // default to true for backward compatibility
    $hasAdvancedInventory = \App\Services\SubscriptionService::hasFeature('batch_operations');
@endphp

@section('header_actions')
    @if($canExportInventory)
        <button type="button" onclick="exportInventory()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    @endif

    @if($canManageInventory)
        <button type="button" onclick="showAddStockModal()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Stock
        </button>
    @endif
@endsection

@section('content')
<section class="space-y-6">
    @if(!$hasAdvancedInventory)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Advanced inventory workflows require Premium plan. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format((int) data_get($inventoryStats ?? [], 'total_products', 0)) }}</p>
            <p class="mt-1 text-xs text-slate-500">Current tenant inventory items</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Low Stock Items</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format((int) data_get($inventoryStats ?? [], 'low_stock_products', 0)) }}</p>
            <p class="mt-1 text-xs text-slate-500">Needs replenishment soon</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Healthy Stock</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format((int) data_get($inventoryStats ?? [], 'healthy_stock_products', 0)) }}</p>
            <p class="mt-1 text-xs text-slate-500">Above minimum thresholds</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Value</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">PHP {{ number_format((float) data_get($inventoryStats ?? [], 'total_value', 0), 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Estimated branch inventory value</p>
        </article>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="heading-font text-lg font-semibold text-slate-900">Stock Levels</h2>
            <input id="inventorySearch" type="text" placeholder="Search product..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-0 transition focus:border-indigo-300 focus:shadow-sm sm:w-56">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">Product</th>
                        <th class="px-3 py-3">Current (kg)</th>
                        <th class="px-3 py-3">Min (kg)</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Last Updated</th>
                        <th class="px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTable" class="divide-y divide-slate-100 text-slate-700">
                    @forelse(($inventoryProducts ?? collect()) as $product)
                        @php
                            $stock = (float) data_get($product->inventory, 'current_stock', 0);
                            $reorderLevel = (float) data_get($product->inventory, 'reorder_level', 0);
                            $statusLabel = $stock <= 0 ? 'Out' : ($stock <= $reorderLevel ? 'Low' : 'Good');
                            $statusClass = $stock <= 0
                                ? 'bg-rose-100 text-rose-700'
                                : ($stock <= $reorderLevel ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                        @endphp
                        <tr>
                            <td class="px-3 py-3 font-medium">{{ $product->name }}</td>
                            <td class="px-3 py-3">{{ number_format($stock, 2) }}</td>
                            <td class="px-3 py-3">{{ number_format($reorderLevel, 2) }}</td>
                            <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td class="px-3 py-3">{{ optional($product->updated_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-3 py-3 text-right">
                                @if($canManageInventory)
                                    <button onclick="addStock('{{ addslashes((string) $product->name) }}')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">+ Stock</button>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500">No inventory records found for this tenant.</td>
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
window.canManageInventory = {{ $canManageInventory ? 'true' : 'false' }};

function notify(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2300, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

function showAddStockModal() {
    notify('Add stock panel is coming next.', 'info');
}

function editStock(productName) {
    notify('Adjusting stock for: ' + productName, 'info');
}

function addStock(productName) {
    if (!window.canManageInventory) {
        notify('You do not have permission to add stock.', 'error');
        return;
    }

    // TODO: call backend API to add stock; currently UI placeholder
    notify('Adding stock for: ' + productName, 'success');
}

function exportInventory() {
    notify('Inventory export started.', 'success');
}

document.getElementById('inventorySearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#inventoryTable tr').forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
@endpush