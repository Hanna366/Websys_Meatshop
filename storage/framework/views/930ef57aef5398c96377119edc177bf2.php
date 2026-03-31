

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
<?php
    $userStoreAction = \Illuminate\Support\Facades\Route::has('tenant.users.store')
        ? route('tenant.users.store')
        : url('/settings/users');
?>

<?php if(session('success')): ?>
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if($errors->has('user_create')): ?>
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        <?php echo e($errors->first('user_create')); ?>

    </div>
<?php endif; ?>

<section class="grid min-w-0 gap-6 lg:grid-cols-12">
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

    <div class="min-w-0 space-y-6 lg:col-span-9">
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
            <div class="mb-4 flex items-center justify-between">
                <h2 class="heading-font text-lg font-semibold text-slate-900">User Management</h2>
            </div>

            <form method="POST" action="<?php echo e($userStoreAction); ?>" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="add_user_name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Full Name</label>
                    <input id="add_user_name" name="name" value="<?php echo e(old('name')); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm" placeholder="Juan Dela Cruz" required>
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="add_user_email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                    <input id="add_user_email" type="email" name="email" value="<?php echo e(old('email')); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm" placeholder="cashier@shop.com" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="add_user_username" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Username (Optional)</label>
                    <input id="add_user_username" name="username" value="<?php echo e(old('username')); ?>" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm" placeholder="auto-generated if empty">
                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="add_user_role" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Role</label>
                    <select id="add_user_role" name="role" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm" required>
                        <?php $__currentLoopData = ($availableRoles ?? ['Administrator', 'Cashier']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($roleOption); ?>" <?php echo e(old('role', 'Cashier') === $roleOption ? 'selected' : ''); ?>><?php echo e($roleOption); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="add_user_password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                    <input id="add_user_password" type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm" minlength="8" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="add_user_password_confirmation" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm Password</label>
                    <input id="add_user_password_confirmation" type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm" minlength="8" required>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add User</button>
                </div>
            </form>

            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><th class="px-3 py-3">Username</th><th class="px-3 py-3">Email</th><th class="px-3 py-3">Role</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Actions</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php $__empty_1 = true; $__currentLoopData = ($tenantUsers ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenantUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $roleLabel = ($tenantRoleTablesReady ?? false) ? optional($tenantUser->roles->first())->name : null;
                                if (!$roleLabel) {
                                    $roleLabel = strtolower((string) $tenantUser->role) === 'owner' ? 'Administrator' : ucfirst((string) $tenantUser->role);
                                }
                                $roleClasses = strtolower($roleLabel) === 'administrator'
                                    ? 'bg-rose-100 text-rose-700'
                                    : 'bg-sky-100 text-sky-700';

                                $status = strtolower((string) ($tenantUser->status ?? 'active'));
                                $statusClasses = $status === 'active'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-200 text-slate-700';
                            ?>
                            <tr>
                                <td class="px-3 py-3"><?php echo e($tenantUser->username); ?></td>
                                <td class="px-3 py-3"><?php echo e($tenantUser->email); ?></td>
                                <td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold <?php echo e($roleClasses); ?>"><?php echo e($roleLabel); ?></span></td>
                                <td class="px-3 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                <td class="px-3 py-3">
                                    <?php if(strtolower($roleLabel) === 'owner'): ?>
                                        <span class="text-xs text-slate-500">Protected</span>
                                    <?php else: ?>
                                        <div class="space-y-2">
                                            <?php
                                                $userUpdateAction = \Illuminate\Support\Facades\Route::has('tenant.users.update')
                                                    ? route('tenant.users.update', $tenantUser->id)
                                                    : url('/settings/users/' . $tenantUser->id);
                                            ?>
                                            <form method="POST" action="<?php echo e($userUpdateAction); ?>" class="grid min-w-0 gap-2 lg:grid-cols-4">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <input type="text" name="name" value="<?php echo e($tenantUser->name); ?>" class="min-w-0 rounded-lg border border-slate-200 px-2 py-1.5 text-xs" required>
                                                <input type="text" name="username" value="<?php echo e($tenantUser->username); ?>" class="min-w-0 rounded-lg border border-slate-200 px-2 py-1.5 text-xs" required>
                                                <input type="email" name="email" value="<?php echo e($tenantUser->email); ?>" class="min-w-0 rounded-lg border border-slate-200 px-2 py-1.5 text-xs" required>
                                                <select name="role" class="min-w-0 rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                                                    <?php $__currentLoopData = ($availableRoles ?? ['Administrator', 'Cashier']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($roleOption); ?>" <?php echo e(strtolower($roleLabel) === strtolower($roleOption) ? 'selected' : ''); ?>><?php echo e($roleOption); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <input type="hidden" name="status" value="<?php echo e($status); ?>">
                                                <div class="flex items-center gap-2 lg:col-span-4">
                                                    <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700">Save</button>
                                                </div>
                                            </form>

                                            <?php
                                                $userStatusAction = \Illuminate\Support\Facades\Route::has('tenant.users.status')
                                                    ? route('tenant.users.status', $tenantUser->id)
                                                    : url('/settings/users/' . $tenantUser->id . '/status');
                                            ?>
                                            <form method="POST" action="<?php echo e($userStatusAction); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="rounded-lg border px-2.5 py-1.5 text-xs font-semibold <?php echo e($status === 'active' ? 'border-rose-200 text-rose-700' : 'border-emerald-200 text-emerald-700'); ?>">
                                                    <?php echo e($status === 'active' ? 'Deactivate' : 'Activate'); ?>

                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-slate-500">No users found for this tenant yet.</td>
                            </tr>
                        <?php endif; ?>
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
<?php echo $__env->make('layouts.tenant', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\Websys_Meatshop\resources\views/settings.blade.php ENDPATH**/ ?>