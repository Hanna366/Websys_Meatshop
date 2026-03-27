

<?php $__env->startSection('title', 'Settings - Meat Shop POS'); ?>
<?php $__env->startSection('page_title', 'Settings'); ?>
<?php $__env->startSection('page_subtitle', 'Configure branch preferences, users, backups, and operations defaults'); ?>

<?php $__env->startSection('header_actions'); ?>
    <button type="button" onclick="notify('Settings reset to last saved values.', 'info')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
        Reset
    </button>
    <button type="button" onclick="notify('Settings saved successfully.', 'success')" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
        <i data-lucide="save" class="h-4 w-4"></i>
        Save Changes
    </button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="grid gap-6 lg:grid-cols-12">
    <aside class="lg:col-span-3">
        <div class="sticky top-24 rounded-3xl border border-white/70 bg-white/90 p-4 shadow-card">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Sections</p>
            <nav class="space-y-1 text-sm">
                <a href="#general" class="block rounded-xl px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">General</a>
                <a href="#business" class="block rounded-xl px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Business</a>
                <a href="#tax" class="block rounded-xl px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Tax and Currency</a>
                <a href="#inventory" class="block rounded-xl px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Inventory</a>
                <a href="#users" class="block rounded-xl px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Users</a>
                <a href="#backup" class="block rounded-xl px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Backup</a>
            </nav>
        </div>
    </aside>

    <div class="space-y-6 lg:col-span-9">
        <section id="general" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">General Settings</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="Meat Shop POS" placeholder="Shop Name">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="admin@meatshop.com" placeholder="Shop Email">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="+63 912 3456" placeholder="Phone">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="123 Market Street, Manila" placeholder="Address">
            </div>
        </section>

        <section id="business" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Business Information</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="Premium Meat Shop Inc.">
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Retail</option><option>Wholesale</option><option>Both</option></select>
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="123-456-789-000">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="BL-2024-12345">
            </div>
            <textarea class="mt-4 w-full rounded-xl border border-slate-200 px-3 py-2.5" rows="3">Premium quality meat products serving the community since 2010.</textarea>
        </section>

        <section id="tax" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Tax and Currency</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Philippine Peso (PHP)</option><option>USD</option><option>EUR</option></select>
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Before (PHP 100.00)</option><option>After (100.00 PHP)</option></select>
                <input type="number" class="rounded-xl border border-slate-200 px-3 py-2.5" value="12" step="0.1" placeholder="VAT Rate">
                <input type="number" class="rounded-xl border border-slate-200 px-3 py-2.5" value="0" step="0.1" placeholder="Service Charge">
            </div>
        </section>

        <section id="inventory" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Inventory Controls</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input type="number" class="rounded-xl border border-slate-200 px-3 py-2.5" value="20" min="1" max="100" placeholder="Low Stock Alert %">
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Email Notification</option><option>SMS Notification</option><option>Both</option><option>None</option></select>
            </div>
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                <label class="flex items-center gap-2"><input type="checkbox" checked class="rounded border-slate-300"> Enable automatic reorder suggestions</label>
                <label class="flex items-center gap-2"><input type="checkbox" checked class="rounded border-slate-300"> Track product expiry dates</label>
                <label class="flex items-center gap-2"><input type="checkbox" class="rounded border-slate-300"> Enable batch tracking</label>
            </div>
        </section>

        <section id="users" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <div class="mb-4 flex items-center justify-between"><h2 class="heading-font text-lg font-semibold text-slate-900">User Management</h2><button type="button" onclick="notify('Add user flow coming next.', 'info')" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add User</button></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><th class="px-3 py-3">Username</th><th class="px-3 py-3">Email</th><th class="px-3 py-3">Role</th><th class="px-3 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr><td class="px-3 py-3">admin</td><td class="px-3 py-3">admin@meatshop.com</td><td class="px-3 py-3"><span class="rounded-full bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700">Administrator</span></td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Active</span></td></tr>
                        <tr><td class="px-3 py-3">cashier1</td><td class="px-3 py-3">cashier1@meatshop.com</td><td class="px-3 py-3"><span class="rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700">Cashier</span></td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Active</span></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="backup" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Backup and Restore</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4"><p class="mb-1 text-sm font-semibold text-slate-800">Manual Backup</p><p class="mb-3 text-xs text-slate-500">Create and download a backup snapshot.</p><button type="button" onclick="notify('Backup download started.', 'success')" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Download Backup</button></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="mb-1 text-sm font-semibold text-slate-800">Restore</p><p class="mb-3 text-xs text-slate-500">Upload an SQL or JSON backup file.</p><input type="file" class="mb-3 block w-full text-sm"><button type="button" onclick="notify('Restore initiated.', 'warning')" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">Restore Backup</button></div>
            </div>
        </section>
    </div>
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
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/settings.blade.php ENDPATH**/ ?>