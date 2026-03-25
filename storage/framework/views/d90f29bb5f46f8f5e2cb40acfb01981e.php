

<?php $__env->startSection('title', 'Inventory - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Inventory'); ?>
<?php $__env->startSection('page_subtitle', 'Track stock movement, reorder points, and inventory value'); ?>

<?php $__env->startSection('header_actions'); ?>
    <?php if(session('permissions.data_export')): ?>
        <button type="button" onclick="exportInventory()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    <?php endif; ?>

    <?php if(session('permissions.max_products') == -1 || session('permissions.max_products') > 30): ?>
        <button type="button" onclick="showAddStockModal()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Stock
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <?php if(session('permissions.max_products') != -1 && session('permissions.max_products') <= 30): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Advanced inventory workflows need Standard plan or higher. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    <?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">32</p>
            <p class="mt-1 text-xs text-slate-500">3 categories monitored</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Low Stock Items</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">5</p>
            <p class="mt-1 text-xs text-slate-500">Needs replenishment today</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Healthy Stock</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">27</p>
            <p class="mt-1 text-xs text-slate-500">Above minimum thresholds</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Value</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">PHP 45,680</p>
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
                    <tr>
                        <td class="px-3 py-3 font-medium">Prime Rib Steak</td>
                        <td class="px-3 py-3">45.5</td>
                        <td class="px-3 py-3">20</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Good</span></td>
                        <td class="px-3 py-3">2024-02-20 08:00</td>
                        <td class="px-3 py-3 text-right"><button onclick="addStock('Prime Rib Steak')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">+ Stock</button></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">Tenderloin</td>
                        <td class="px-3 py-3">8.4</td>
                        <td class="px-3 py-3">10</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Low</span></td>
                        <td class="px-3 py-3">2024-02-20 08:00</td>
                        <td class="px-3 py-3 text-right"><button onclick="addStock('Tenderloin')" class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">Restock</button></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">Soup Bones</td>
                        <td class="px-3 py-3">156.7</td>
                        <td class="px-3 py-3">50</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">Good</span></td>
                        <td class="px-3 py-3">2024-02-20 08:00</td>
                        <td class="px-3 py-3 text-right"><button onclick="editStock('Soup Bones')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Adjust</button></td>
                    </tr>
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

function showAddStockModal() {
    notify('Add stock panel is coming next.', 'info');
}

function editStock(productName) {
    notify('Adjusting stock for: ' + productName, 'info');
}

function addStock(productName) {
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/inventory.blade.php ENDPATH**/ ?>