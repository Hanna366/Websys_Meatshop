@extends('layouts.tenant')

@section('title', 'Reports - Meat Shop POS')
@section('page_title', 'Reports')
@section('page_subtitle', 'Analyze sales performance, inventory flow, and category trends')

@section('header_actions')
    @if(session('permissions.advanced_analytics'))
        <button type="button" onclick="generateAdvancedReport()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="line-chart" class="h-4 w-4"></i>
            Advanced Report
        </button>
        <button type="button" onclick="exportReports()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    @else
        <button type="button" disabled title="Advanced analytics requires Premium plan." class="inline-flex cursor-not-allowed items-center gap-2 rounded-full bg-slate-300 px-4 py-2 text-sm font-semibold text-white">
            <i data-lucide="line-chart" class="h-4 w-4"></i>
            Advanced Report
        </button>
    @endif
@endsection

@section('content')
<section class="space-y-6">
    @if(!session('permissions.advanced_analytics'))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Advanced analytics and report exports require Premium plan. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    @endif

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Filters</h2>
        <div class="grid gap-3 md:grid-cols-4">
            <select class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-300">
                <option>Today</option><option>This Week</option><option>This Month</option><option>This Year</option>
            </select>
            <select class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-300">
                <option>Sales Report</option><option>Inventory Report</option><option>Customer Report</option><option>Financial Summary</option>
            </select>
            <select class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-300">
                <option>All Categories</option><option>Beef</option><option>Pork</option><option>Poultry</option><option>Byproducts</option>
            </select>
            <button type="button" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Apply</button>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Revenue</p><p class="mt-2 text-2xl font-bold text-slate-900">PHP 345,680</p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Sales</p><p class="mt-2 text-2xl font-bold text-emerald-600">247</p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Avg Sale</p><p class="mt-2 text-2xl font-bold text-indigo-600">PHP 1,400</p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Product</p><p class="mt-2 text-2xl font-bold text-amber-600">Prime Rib</p></article>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card lg:col-span-2">
            <h3 class="mb-4 text-base font-semibold text-slate-800">Sales Trend</h3>
            <canvas id="salesChart" height="120"></canvas>
        </section>
        <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <h3 class="mb-4 text-base font-semibold text-slate-800">Category Split</h3>
            <canvas id="categoryChart" height="120"></canvas>
        </section>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <h3 class="mb-4 text-base font-semibold text-slate-800">Detailed Sales</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><th class="px-3 py-3">Date</th><th class="px-3 py-3">Product</th><th class="px-3 py-3">Qty (kg)</th><th class="px-3 py-3">Unit Price</th><th class="px-3 py-3">Total</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr><td class="px-3 py-3">2024-02-20</td><td class="px-3 py-3">Prime Rib Steak</td><td class="px-3 py-3">15.5</td><td class="px-3 py-3">PHP 2,870</td><td class="px-3 py-3 font-semibold">PHP 44,485</td><td class="px-3 py-3">John Martinez</td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Completed</span></td></tr>
                    <tr><td class="px-3 py-3">2024-02-20</td><td class="px-3 py-3">Ribeye</td><td class="px-3 py-3">8.2</td><td class="px-3 py-3">PHP 3,570</td><td class="px-3 py-3 font-semibold">PHP 29,274</td><td class="px-3 py-3">Maria Santos</td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Completed</span></td></tr>
                    <tr><td class="px-3 py-3">2024-02-19</td><td class="px-3 py-3">Brisket</td><td class="px-3 py-3">22.8</td><td class="px-3 py-3">PHP 980</td><td class="px-3 py-3 font-semibold">PHP 22,344</td><td class="px-3 py-3">Linda Reyes</td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Completed</span></td></tr>
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toast(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2300, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

function generateAdvancedReport() { toast('Generating advanced report...', 'info'); }
function exportReports() { toast('Report export started.', 'success'); }

const salesCtx = document.getElementById('salesChart');
if (salesCtx && window.Chart) {
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales (PHP)',
                data: [12500, 19800, 15200, 22300, 18900, 24600, 31200],
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                tension: 0.35,
                fill: true
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
}

const categoryCtx = document.getElementById('categoryChart');
if (categoryCtx && window.Chart) {
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Beef', 'Pork', 'Poultry', 'Lamb', 'Byproducts'],
            datasets: [{
                data: [45, 25, 15, 10, 5],
                backgroundColor: ['#dc2626', '#f97316', '#3b82f6', '#eab308', '#14b8a6']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}
</script>
@endpush