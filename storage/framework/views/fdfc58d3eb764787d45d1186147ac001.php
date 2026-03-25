

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page_title', 'Dashboard'); ?>
<?php $__env->startSection('page_subtitle', 'Track branch performance, sales flow, and stock health'); ?>

<?php $__env->startSection('header_actions'); ?>
    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" onclick="exportDashboard()">
        <i data-lucide="download" class="h-4 w-4"></i>
        Export
    </button>
    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" onclick="printDashboard()">
        <i data-lucide="printer" class="h-4 w-4"></i>
        Print
    </button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-1 text-lg font-semibold text-slate-900">Welcome back<?php echo e(session('user.name') ? ', ' . session('user.name') : ''); ?></h2>
                <p class="mb-0 text-sm text-slate-500"><?php echo e(session('user.email', 'No email available')); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if(isset($tenant) && $tenant): ?>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Branch: <?php echo e($tenant->business_name ?? $tenant->tenant_id); ?></span>
                <?php endif; ?>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Plan: <?php echo e(session('user.plan', ucfirst($tenant->plan ?? 'Basic'))); ?></span>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">Today's Sales</p>
                    <h3 class="heading-font text-3xl font-semibold text-slate-900">$12,456</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                    <i data-lucide="dollar-sign" class="h-5 w-5"></i>
                </span>
            </div>
            <div class="mt-4 h-1.5 rounded-full bg-gradient-to-r from-indigo-500/60 to-indigo-100"></div>
        </article>

        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">Products</p>
                    <h3 class="heading-font text-3xl font-semibold text-emerald-700">245</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <i data-lucide="package" class="h-5 w-5"></i>
                </span>
            </div>
            <div class="mt-4 h-1.5 rounded-full bg-gradient-to-r from-emerald-500/60 to-emerald-100"></div>
        </article>

        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">Low Stock Items</p>
                    <h3 class="heading-font text-3xl font-semibold text-amber-600">8</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <i data-lucide="triangle-alert" class="h-5 w-5"></i>
                </span>
            </div>
            <div class="mt-4 h-1.5 rounded-full bg-gradient-to-r from-amber-500/60 to-amber-100"></div>
        </article>

        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">Customers</p>
                    <h3 class="heading-font text-3xl font-semibold text-teal-700">1,234</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                    <i data-lucide="users" class="h-5 w-5"></i>
                </span>
            </div>
            <div class="mt-4 h-1.5 rounded-full bg-gradient-to-r from-teal-500/60 to-teal-100"></div>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex items-center justify-between border-b border-slate-200/70 px-5 py-4">
            <div>
                <h3 class="heading-font mb-0 text-lg font-semibold text-slate-900">Recent Sales</h3>
                <p class="mb-0 text-sm text-slate-500">Latest orders processed by this branch.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Order ID</th>
                        <th class="px-5 py-3.5 font-semibold">Customer</th>
                        <th class="px-5 py-3.5 font-semibold">Products</th>
                        <th class="px-5 py-3.5 font-semibold">Total</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php
                        $sales = [
                            ['id' => 'ORD-001', 'customer' => 'John Smith', 'products' => 'Beef, Chicken, Pork', 'total' => '$156.78', 'status' => 'Completed', 'date' => '2024-02-20'],
                            ['id' => 'ORD-002', 'customer' => 'Sarah Johnson', 'products' => 'Lamb, Turkey', 'total' => '$89.45', 'status' => 'Processing', 'date' => '2024-02-20'],
                            ['id' => 'ORD-003', 'customer' => 'Mike Wilson', 'products' => 'Beef, Pork', 'total' => '$234.12', 'status' => 'Completed', 'date' => '2024-02-19'],
                            ['id' => 'ORD-004', 'customer' => 'Emily Davis', 'products' => 'Chicken, Fish', 'total' => '$67.89', 'status' => 'Pending', 'date' => '2024-02-19'],
                            ['id' => 'ORD-005', 'customer' => 'Robert Brown', 'products' => 'Beef, Lamb, Turkey', 'total' => '$312.45', 'status' => 'Completed', 'date' => '2024-02-18'],
                        ];
                    ?>

                    <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = [
                                'Completed' => 'bg-emerald-50 text-emerald-700',
                                'Processing' => 'bg-amber-50 text-amber-700',
                                'Pending' => 'bg-sky-50 text-sky-700',
                            ][$sale['status']] ?? 'bg-slate-100 text-slate-700';
                        ?>
                        <tr class="transition hover:bg-indigo-50/40">
                            <td class="px-5 py-4 font-semibold text-slate-900">#<?php echo e($sale['id']); ?></td>
                            <td class="px-5 py-4"><?php echo e($sale['customer']); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e($sale['products']); ?></td>
                            <td class="px-5 py-4 font-medium"><?php echo e($sale['total']); ?></td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($sale['status']); ?></span></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e($sale['date']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No sales yet. Start processing transactions to see activity.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function exportDashboard() {
        const payload = {
            user: <?php if(session('user')): ?> {
                email: '<?php echo e(session('user.email')); ?>',
                plan: '<?php echo e(session('user.plan')); ?>'
            } <?php else: ?> null <?php endif; ?>,
            exported_at: new Date().toISOString(),
            stats: {
                today_sales: '$12,456',
                products: 245,
                low_stock_items: 8,
                customers: 1234
            }
        };

        const dataStr = JSON.stringify(payload, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
        const fileName = 'tenant_dashboard_export_' + new Date().toISOString().split('T')[0] + '.json';

        const link = document.createElement('a');
        link.setAttribute('href', dataUri);
        link.setAttribute('download', fileName);
        link.click();

        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            icon: 'success',
            title: 'Dashboard exported successfully.'
        });
    }

    function printDashboard() {
        window.print();
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/dashboard/index.blade.php ENDPATH**/ ?>