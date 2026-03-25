

<?php $__env->startSection('title', 'Products - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Products'); ?>
<?php $__env->startSection('page_subtitle', 'Manage meat cuts, byproducts, and pricing across your branch'); ?>

<?php $__env->startSection('header_actions'); ?>
    <?php if(session('permissions.csv_export')): ?>
        <button type="button" onclick="exportProducts('csv')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="download" class="h-4 w-4"></i>
            Export CSV
        </button>
    <?php else: ?>
        <button type="button" disabled title="Export requires Standard plan or higher." class="inline-flex cursor-not-allowed items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
            <i data-lucide="download" class="h-4 w-4"></i>
            Export CSV
        </button>
    <?php endif; ?>

    <?php if(session('permissions.max_products') == -1 || session('permissions.max_products') > 50): ?>
        <button type="button" onclick="showAddProductModal()" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Product
        </button>
    <?php else: ?>
        <button type="button" disabled title="Product limit reached. Upgrade to add more products." class="inline-flex cursor-not-allowed items-center gap-2 rounded-full bg-slate-300 px-4 py-2 text-sm font-semibold text-white">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Product
        </button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-6">
    <?php if(session('permissions.max_products') != -1): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Product cap: <span class="font-semibold"><?php echo e(session('permissions.max_products')); ?></span>. Upgrade to manage a broader catalog.
        </div>
    <?php endif; ?>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">32</p>
            <p class="mt-1 text-xs text-slate-500">24 prime cuts, 8 byproducts</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Low Stock</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">5</p>
            <p class="mt-1 text-xs text-slate-500">Reorder this week</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Avg Margin</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">31.4%</p>
            <p class="mt-1 text-xs text-slate-500">Across top-selling items</p>
        </article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catalog Health</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">Good</p>
            <p class="mt-1 text-xs text-slate-500">No out-of-stock products</p>
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
                    <tr>
                        <td class="px-3 py-3 font-medium">Prime Rib Steak</td>
                        <td class="px-3 py-3">Beef</td>
                        <td class="px-3 py-3 font-semibold">PHP 2,870</td>
                        <td class="px-3 py-3">45 kg</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">In Stock</span></td>
                        <td class="px-3 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <button onclick="editProduct('Prime Rib Steak')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                <button onclick="deleteProduct('Prime Rib Steak')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">Ribeye</td>
                        <td class="px-3 py-3">Beef</td>
                        <td class="px-3 py-3 font-semibold">PHP 3,570</td>
                        <td class="px-3 py-3">18 kg</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">In Stock</span></td>
                        <td class="px-3 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <button onclick="editProduct('Ribeye')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                <button onclick="deleteProduct('Ribeye')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">Tenderloin</td>
                        <td class="px-3 py-3">Beef</td>
                        <td class="px-3 py-3 font-semibold">PHP 4,020</td>
                        <td class="px-3 py-3">8 kg</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Low Stock</span></td>
                        <td class="px-3 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <button onclick="editProduct('Tenderloin')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                <button onclick="deleteProduct('Tenderloin')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-3 py-3 font-medium">Soup Bones</td>
                        <td class="px-3 py-3">Byproducts</td>
                        <td class="px-3 py-3 font-semibold">PHP 220</td>
                        <td class="px-3 py-3">157 kg</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">High Volume</span></td>
                        <td class="px-3 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <button onclick="editProduct('Soup Bones')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                <button onclick="deleteProduct('Soup Bones')" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/products.blade.php ENDPATH**/ ?>