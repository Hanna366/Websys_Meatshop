

<?php $__env->startSection('title', 'Reports - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Reports'); ?>
<?php $__env->startSection('page_subtitle', 'Analyze sales performance, inventory flow, and category trends'); ?>

<?php
    $canAdvancedAnalytics = \App\Services\SubscriptionService::hasFeature('advanced_analytics');
    $canExportReports = \App\Services\SubscriptionService::hasFeature('data_export')
        || \App\Services\SubscriptionService::hasFeature('export_csv');
?>

<?php $__env->startSection('header_actions'); ?>
    <?php if($canAdvancedAnalytics): ?>
        <button type="button" onclick="generateAdvancedReport()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="line-chart" class="h-4 w-4"></i>
            Advanced Report
        </button>
        <?php if($canExportReports): ?>
        <button type="button" onclick="exportReports()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
        <?php endif; ?>
    <?php else: ?>
        <button type="button" disabled title="Advanced analytics requires Premium plan." class="inline-flex cursor-not-allowed items-center gap-2 rounded-full bg-slate-300 px-4 py-2 text-sm font-semibold text-white">
            <i data-lucide="line-chart" class="h-4 w-4"></i>
            Advanced Report
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <?php if(!$canAdvancedAnalytics): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Advanced analytics and report exports require Premium plan. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    <?php endif; ?>

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
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Revenue</p><p class="mt-2 text-2xl font-bold text-slate-900">PHP <?php echo e(number_format((float) data_get($reportStats ?? [], 'total_revenue', 0), 2)); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Sales</p><p class="mt-2 text-2xl font-bold text-emerald-600"><?php echo e(number_format((int) data_get($reportStats ?? [], 'total_sales', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Avg Sale</p><p class="mt-2 text-2xl font-bold text-indigo-600">PHP <?php echo e(number_format((float) data_get($reportStats ?? [], 'average_sale', 0), 2)); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Top Product</p><p class="mt-2 text-2xl font-bold text-amber-600"><?php echo e((string) data_get($reportStats ?? [], 'top_product', 'No sales yet')); ?></p></article>
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
                    <?php $__empty_1 = true; $__currentLoopData = ($detailedSales ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $firstItem = collect($sale->items ?? [])->first() ?? [];
                            $status = ucfirst((string) ($sale->status ?? 'pending'));
                            $statusClass = $status === 'Completed'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($status === 'Voided' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700');
                        ?>
                        <tr>
                            <td class="px-3 py-3"><?php echo e(optional($sale->created_at)->format('Y-m-d') ?? '-'); ?></td>
                            <td class="px-3 py-3"><?php echo e((string) ($firstItem['name'] ?? 'Mixed order')); ?></td>
                            <td class="px-3 py-3"><?php echo e(number_format((float) ($firstItem['quantity'] ?? 0), 2)); ?></td>
                            <td class="px-3 py-3">PHP <?php echo e(number_format((float) ($firstItem['unit_price'] ?? 0), 2)); ?></td>
                            <td class="px-3 py-3 font-semibold">PHP <?php echo e(number_format((float) ($sale->grand_total ?? $sale->total ?? 0), 2)); ?></td>
                            <td class="px-3 py-3"><?php echo e(data_get($salesCustomerNames ?? [], (string) $sale->customer_id, 'Walk-in Customer')); ?></td>
                            <td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-slate-500">No completed sales data to report yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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

const salesTrend = <?php echo json_encode($salesTrend ?? [], 15, 512) ?>;
const categorySplit = <?php echo json_encode($categorySplit ?? [], 15, 512) ?>;

const salesCtx = document.getElementById('salesChart');
if (salesCtx && window.Chart) {
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesTrend.map((entry) => entry.label),
            datasets: [{
                label: 'Sales (PHP)',
                data: salesTrend.map((entry) => Number(entry.value || 0)),
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
            labels: categorySplit.map((entry) => entry.label),
            datasets: [{
                data: categorySplit.map((entry) => Number(entry.value || 0)),
                backgroundColor: ['#dc2626', '#f97316', '#3b82f6', '#eab308', '#14b8a6']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/reports.blade.php ENDPATH**/ ?>