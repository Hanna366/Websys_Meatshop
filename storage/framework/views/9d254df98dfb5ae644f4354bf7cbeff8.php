

<?php $__env->startSection('title', 'Customers - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Customers'); ?>
<?php $__env->startSection('page_subtitle', 'Track loyal buyers, spending trends, and customer engagement'); ?>

<?php
    $canExportCustomers = \App\Services\SubscriptionService::hasFeature('data_export')
        || \App\Services\SubscriptionService::hasFeature('export_csv');
    $canManageCustomers = \App\Services\SubscriptionService::hasFeature('customer_management');
?>

<?php $__env->startSection('header_actions'); ?>
    <?php if($canExportCustomers): ?>
        <button type="button" onclick="notify('Customer export started.', 'success')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    <?php endif; ?>

    <?php if($canManageCustomers): ?>
        <button type="button" onclick="notify('New customer form will open here.', 'info')" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="user-plus" class="h-4 w-4"></i>
            Add Customer
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <?php if(!$canManageCustomers): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Customer management requires Standard plan or higher. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    <?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Customers</p><p class="mt-2 text-2xl font-bold text-slate-900"><?php echo e(number_format((int) data_get($customerStats ?? [], 'total', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active This Month</p><p class="mt-2 text-2xl font-bold text-emerald-600"><?php echo e(number_format((int) data_get($customerStats ?? [], 'active_this_month', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">New This Week</p><p class="mt-2 text-2xl font-bold text-indigo-600"><?php echo e(number_format((int) data_get($customerStats ?? [], 'new_this_week', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">VIP Customers</p><p class="mt-2 text-2xl font-bold text-amber-600"><?php echo e(number_format((int) data_get($customerStats ?? [], 'vip', 0))); ?></p></article>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="heading-font text-lg font-semibold text-slate-900">Customer List</h2>
            <input id="customerSearch" type="text" placeholder="Search customer..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-0 transition focus:border-indigo-300 focus:shadow-sm sm:w-56">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3">Name</th>
                        <th class="px-3 py-3">Contact</th>
                        <th class="px-3 py-3">Orders</th>
                        <th class="px-3 py-3">Total Spent</th>
                        <th class="px-3 py-3">Status</th>
                    </tr>
                </thead>
                <tbody id="customersTable" class="divide-y divide-slate-100 text-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = ($customerRows ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $name = trim(implode(' ', array_filter([
                                (string) ($customer->first_name ?? data_get($customer->personal_info, 'first_name', '')),
                                (string) ($customer->last_name ?? data_get($customer->personal_info, 'last_name', '')),
                            ])));
                            $phone = (string) ($customer->phone ?? data_get($customer->personal_info, 'phone', '-'));
                            $orders = (int) data_get($customer->purchasing_history, 'total_orders', 0);
                            $spent = (float) data_get($customer->purchasing_history, 'total_spent', 0);
                            $status = ucfirst((string) ($customer->status ?? 'active'));
                            $statusClass = in_array(strtolower($status), ['vip', 'gold', 'platinum'], true)
                                ? 'bg-amber-100 text-amber-700'
                                : (strtolower($status) === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700');
                        ?>
                        <tr>
                            <td class="px-3 py-3">#<?php echo e($customer->customer_code ?: ('CUS-' . $customer->id)); ?></td>
                            <td class="px-3 py-3 font-medium"><?php echo e($name !== '' ? $name : ('Customer #' . $customer->id)); ?></td>
                            <td class="px-3 py-3"><?php echo e($phone !== '' ? $phone : '-'); ?></td>
                            <td class="px-3 py-3"><?php echo e(number_format($orders)); ?></td>
                            <td class="px-3 py-3 font-semibold">PHP <?php echo e(number_format($spent, 2)); ?></td>
                            <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500">No customers found for this tenant yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function notify(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2300, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

document.getElementById('customerSearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#customersTable tr').forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/customers.blade.php ENDPATH**/ ?>