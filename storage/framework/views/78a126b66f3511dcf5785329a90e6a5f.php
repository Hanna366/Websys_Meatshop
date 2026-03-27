

<?php $__env->startSection('title', 'Suppliers - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Suppliers'); ?>
<?php $__env->startSection('page_subtitle', 'Manage vendor relationships, status, and incoming deliveries'); ?>

<?php
    $canExportSuppliers = \App\Services\SubscriptionService::hasFeature('data_export')
        || \App\Services\SubscriptionService::hasFeature('export_csv');
    $canManageSuppliers = \App\Services\SubscriptionService::hasFeature('supplier_management');
?>

<?php $__env->startSection('header_actions'); ?>
    <?php if($canExportSuppliers): ?>
        <button type="button" onclick="notify('Supplier export started.', 'success')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    <?php endif; ?>

    <?php if($canManageSuppliers): ?>
        <button type="button" onclick="notify('New supplier form will open here.', 'info')" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Supplier
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <?php if(!$canManageSuppliers): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Supplier management requires Standard plan or higher. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    <?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Suppliers</p><p class="mt-2 text-2xl font-bold text-slate-900"><?php echo e(number_format((int) data_get($supplierStats ?? [], 'total', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active</p><p class="mt-2 text-2xl font-bold text-emerald-600"><?php echo e(number_format((int) data_get($supplierStats ?? [], 'active', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Orders</p><p class="mt-2 text-2xl font-bold text-indigo-600"><?php echo e(number_format((int) data_get($supplierStats ?? [], 'pending', 0))); ?></p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deliveries Today</p><p class="mt-2 text-2xl font-bold text-amber-600"><?php echo e(number_format((int) data_get($supplierStats ?? [], 'deliveries_today', 0))); ?></p></article>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="heading-font text-lg font-semibold text-slate-900">Supplier List</h2>
            <input id="supplierSearch" type="text" placeholder="Search supplier..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-0 transition focus:border-indigo-300 focus:shadow-sm sm:w-56">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3">Company</th>
                        <th class="px-3 py-3">Contact</th>
                        <th class="px-3 py-3">Phone</th>
                        <th class="px-3 py-3">Products</th>
                        <th class="px-3 py-3">Status</th>
                    </tr>
                </thead>
                <tbody id="suppliersTable" class="divide-y divide-slate-100 text-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = ($supplierRows ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $companyName = (string) ($supplier->name ?? data_get($supplier->business_info, 'name', 'Supplier #' . $supplier->id));
                            $contactName = (string) (data_get($supplier->details, 'contact_person')
                                ?? data_get($supplier->business_details, 'contact_person')
                                ?? data_get($supplier->business_info, 'name', '-'));
                            $phone = (string) ($supplier->phone ?? data_get($supplier->business_info, 'phone', '-'));
                            $products = data_get($supplier->product_categories, '0')
                                ? implode(', ', array_slice((array) $supplier->product_categories, 0, 2))
                                : ((string) (data_get($supplier->details, 'product_line') ?? 'N/A'));
                            $status = ucfirst((string) ($supplier->status ?? 'active'));
                            $statusClass = strtolower($status) === 'active'
                                ? 'bg-emerald-100 text-emerald-700'
                                : (strtolower($status) === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700');
                        ?>
                        <tr>
                            <td class="px-3 py-3">#<?php echo e($supplier->supplier_code ?: ('SUP-' . $supplier->id)); ?></td>
                            <td class="px-3 py-3 font-medium"><?php echo e($companyName); ?></td>
                            <td class="px-3 py-3"><?php echo e($contactName !== '' ? $contactName : '-'); ?></td>
                            <td class="px-3 py-3"><?php echo e($phone !== '' ? $phone : '-'); ?></td>
                            <td class="px-3 py-3"><?php echo e($products !== '' ? $products : 'N/A'); ?></td>
                            <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500">No suppliers found for this tenant yet.</td>
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

document.getElementById('supplierSearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#suppliersTable tr').forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/suppliers.blade.php ENDPATH**/ ?>