@extends('layouts.tenant')

@section('title', 'Sales - Meat Shop POS')
@section('page_title', 'Sales')
@section('page_subtitle', 'Monitor transactions, payment channels, and daily branch revenue')

@php
    $canExportSales = \App\Services\SubscriptionService::hasFeature('data_export')
        || \App\Services\SubscriptionService::hasFeature('export_csv');
    $canAccessPos = \App\Services\SubscriptionService::hasFeature('pos_access');
@endphp

@section('header_actions')
    @if($canExportSales)
        <button type="button" onclick="exportSales()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    @endif

    @if($canAccessPos)
        <button type="button" onclick="showNewSaleModal()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            New Sale
        </button>
    @endif
@endsection

@section('content')
<section class="space-y-6">
    @if(!$canAccessPos)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            POS functionality requires Standard plan or higher. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Today</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">PHP {{ number_format((float) data_get($salesStats ?? [], 'today_revenue', 0), 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Completed sales for today</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">This Week</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">PHP {{ number_format((float) data_get($salesStats ?? [], 'week_revenue', 0), 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Current week revenue</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">This Month</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">PHP {{ number_format((float) data_get($salesStats ?? [], 'month_revenue', 0), 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Current month revenue</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transactions</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format((int) data_get($salesStats ?? [], 'month_transactions', 0)) }}</p>
            <p class="mt-1 text-xs text-slate-500">Average order PHP {{ number_format((float) data_get($salesStats ?? [], 'average_order', 0), 2) }}</p>
        </article>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="heading-font text-lg font-semibold text-slate-900">Recent Sales</h2>
            <input id="salesSearch" type="text" placeholder="Search by order/customer" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-0 transition focus:border-indigo-300 focus:shadow-sm sm:w-64">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">Order ID</th>
                        <th class="px-3 py-3">Customer</th>
                        <th class="px-3 py-3">Items</th>
                        <th class="px-3 py-3">Total</th>
                        <th class="px-3 py-3">Payment</th>
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="salesTable" class="divide-y divide-slate-100 text-slate-700">
                    @forelse(($salesRows ?? collect()) as $sale)
                        @php
                            $saleCode = $sale->sale_code ?: ('SAL-' . $sale->id);
                            $itemsCount = collect($sale->items ?? [])->count();
                            $status = ucfirst((string) ($sale->status ?? 'pending'));
                            $statusClass = [
                                'Completed' => 'bg-emerald-100 text-emerald-700',
                                'Pending' => 'bg-amber-100 text-amber-700',
                                'Voided' => 'bg-rose-100 text-rose-700',
                            ][$status] ?? 'bg-slate-100 text-slate-700';
                        @endphp
                        <tr>
                            <td class="px-3 py-3 font-medium">#{{ $saleCode }}</td>
                            <td class="px-3 py-3">{{ data_get($salesCustomerNames ?? [], (string) $sale->customer_id, 'Walk-in Customer') }}</td>
                            <td class="px-3 py-3">{{ $itemsCount }} item{{ $itemsCount === 1 ? '' : 's' }}</td>
                            <td class="px-3 py-3 font-semibold">PHP {{ number_format((float) ($sale->grand_total ?? $sale->total ?? 0), 2) }}</td>
                            <td class="px-3 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">N/A</span></td>
                            <td class="px-3 py-3">{{ optional($sale->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span></td>
                            <td class="px-3 py-3 text-right"><button onclick="viewSale('#{{ $saleCode }}')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">View</button></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-sm text-slate-500">No sales found for this tenant yet.</td>
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
function notify(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2400, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

function showNewSaleModal() {
    notify('New sale flow will open here.', 'info');
}

function viewSale(saleId) {
    notify('Viewing details for ' + saleId, 'info');
}

function printSale(saleId) {
    notify('Preparing receipt for ' + saleId, 'success');
}

function exportSales() {
    notify('Sales export started.', 'success');
}

document.getElementById('salesSearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#salesTable tr').forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
@endpush