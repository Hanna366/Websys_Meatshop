

<?php $__env->startSection('title', 'Sales - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Sales'); ?>
<?php $__env->startSection('page_subtitle', 'Monitor transactions, payment channels, and daily branch revenue'); ?>

<?php $__env->startSection('header_actions'); ?>
    <?php if(session('permissions.data_export')): ?>
        <button type="button" onclick="exportSales()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    <?php endif; ?>

    <?php if(session('permissions.pos_access')): ?>
        <button type="button" onclick="showNewSaleModal()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            New Sale
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <?php if(!session('permissions.pos_access')): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            POS functionality requires Standard plan or higher. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    <?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Today</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">PHP 12,450</p>
            <p class="mt-1 text-xs text-slate-500">36 completed transactions</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">This Week</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">PHP 87,320</p>
            <p class="mt-1 text-xs text-slate-500">+8.2% from last week</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">This Month</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">PHP 345,680</p>
            <p class="mt-1 text-xs text-slate-500">On track for target</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transactions</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">247</p>
            <p class="mt-1 text-xs text-slate-500">Average order PHP 1,399</p>
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
                    <tr>
                        <td class="px-3 py-3 font-medium">#S001</td>
                        <td class="px-3 py-3">John Martinez</td>
                        <td class="px-3 py-3">3 items</td>
                        <td class="px-3 py-3 font-semibold">PHP 8,450</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Cash</span></td>
                        <td class="px-3 py-3">2024-02-20 14:30</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Completed</span></td>
                        <td class="px-3 py-3 text-right"><button onclick="viewSale('#S001')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">View</button></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">#S002</td>
                        <td class="px-3 py-3">Maria Santos</td>
                        <td class="px-3 py-3">5 items</td>
                        <td class="px-3 py-3 font-semibold">PHP 12,340</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">Card</span></td>
                        <td class="px-3 py-3">2024-02-20 13:45</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Completed</span></td>
                        <td class="px-3 py-3 text-right"><button onclick="printSale('#S002')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Receipt</button></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">#S003</td>
                        <td class="px-3 py-3">Roberto Cruz</td>
                        <td class="px-3 py-3">2 items</td>
                        <td class="px-3 py-3 font-semibold">PHP 5,680</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">GCash</span></td>
                        <td class="px-3 py-3">2024-02-20 12:20</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Completed</span></td>
                        <td class="px-3 py-3 text-right"><button onclick="viewSale('#S003')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">View</button></td>
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views\sales.blade.php ENDPATH**/ ?>