

<?php $__env->startSection('content'); ?>
<?php
    $subscription = is_array($tenant->subscription) ? $tenant->subscription : [];
    $periodStart = $subscription['current_period_start'] ?? optional($tenant->plan_started_at)->toDateString();
    $periodEnd = $subscription['current_period_end'] ?? optional($tenant->plan_ends_at)->toDateString();
    $address = is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : ($tenant->business_address ?? '');
?>

<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-1 text-2xl font-semibold text-slate-900">Tenant Details</h2>
                <p class="mb-0 text-sm text-slate-500">Manage tenant profile, access lifecycle, and subscription periods.</p>
            </div>
            <a href="<?php echo e(route('tenants.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Back to list
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="mb-0 list-disc ps-4">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card xl:col-span-2">
            <h3 class="heading-font mb-4 text-lg font-semibold text-slate-900">Tenant Profile</h3>

            <form method="POST" action="<?php echo e(route('tenants.update', $tenant->tenant_id)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Business Name</label>
                        <input type="text" name="business_name" value="<?php echo e(old('business_name', $tenant->business_name)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Business Email</label>
                        <input type="email" name="business_email" value="<?php echo e(old('business_email', $tenant->business_email)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Business Phone</label>
                        <input type="text" name="business_phone" value="<?php echo e(old('business_phone', $tenant->business_phone)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Domain</label>
                        <input type="text" name="domain" value="<?php echo e(old('domain', $tenant->domain)); ?>" placeholder="branch.localhost" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Admin Name</label>
                        <input type="text" name="admin_name" value="<?php echo e(old('admin_name', $tenant->admin_name)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Admin Email</label>
                        <input type="email" name="admin_email" value="<?php echo e(old('admin_email', $tenant->admin_email)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Business Address</label>
                    <textarea name="business_address" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"><?php echo e(old('business_address', $address)); ?></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-600 hover:text-white">Save Profile</button>
                </div>
            </form>
        </section>

        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                <h3 class="heading-font mb-4 text-base font-semibold text-slate-900">Lifecycle Status</h3>
                <form method="POST" action="<?php echo e(route('tenants.updateStatus', $tenant->tenant_id)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tenant Status</label>
                        <select name="status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="active" <?php echo e(($tenant->status ?? 'active') === 'active' ? 'selected' : ''); ?>>Active</option>
                            <option value="inactive" <?php echo e(($tenant->status ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                            <option value="suspended" <?php echo e(($tenant->status ?? '') === 'suspended' ? 'selected' : ''); ?>>Suspended</option>
                            <option value="unpaid" <?php echo e(($tenant->status ?? '') === 'unpaid' ? 'selected' : ''); ?>>Unpaid</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Payment Status</label>
                        <select name="payment_status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="paid" <?php echo e(($tenant->payment_status ?? 'paid') === 'paid' ? 'selected' : ''); ?>>Paid</option>
                            <option value="unpaid" <?php echo e(($tenant->payment_status ?? '') === 'unpaid' ? 'selected' : ''); ?>>Unpaid</option>
                            <option value="overdue" <?php echo e(($tenant->payment_status ?? '') === 'overdue' ? 'selected' : ''); ?>>Overdue</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Suspension Message</label>
                        <input type="text" name="suspended_message" value="<?php echo e(old('suspended_message', $tenant->suspended_message ?? 'Please contact your administrator.')); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-600 hover:text-white">Update Lifecycle</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                <h3 class="heading-font mb-4 text-base font-semibold text-slate-900">Subscription</h3>
                <form method="POST" action="<?php echo e(route('tenants.updateSubscription', $tenant->tenant_id)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Plan</label>
                        <select name="plan" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="basic" <?php echo e(($tenant->plan ?? 'basic') === 'basic' ? 'selected' : ''); ?>>Basic</option>
                            <option value="standard" <?php echo e(($tenant->plan ?? '') === 'standard' ? 'selected' : ''); ?>>Standard</option>
                            <option value="premium" <?php echo e(($tenant->plan ?? '') === 'premium' ? 'selected' : ''); ?>>Premium</option>
                            <option value="enterprise" <?php echo e(($tenant->plan ?? '') === 'enterprise' ? 'selected' : ''); ?>>Enterprise</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Billing Cycle</label>
                        <select name="billing_cycle" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="monthly" <?php echo e(($subscription['billing_cycle'] ?? 'monthly') === 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                            <option value="annual" <?php echo e(($subscription['billing_cycle'] ?? '') === 'annual' ? 'selected' : ''); ?>>Annual</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Subscription Status</label>
                        <select name="subscription_status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="active" <?php echo e(($subscription['status'] ?? 'active') === 'active' ? 'selected' : ''); ?>>Active</option>
                            <option value="unpaid" <?php echo e(($subscription['status'] ?? '') === 'unpaid' ? 'selected' : ''); ?>>Unpaid</option>
                            <option value="expired" <?php echo e(($subscription['status'] ?? '') === 'expired' ? 'selected' : ''); ?>>Expired</option>
                            <option value="cancelled" <?php echo e(($subscription['status'] ?? '') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Current Period Start</label>
                            <input type="date" name="current_period_start" value="<?php echo e(old('current_period_start', $periodStart)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Current Period End</label>
                            <input type="date" name="current_period_end" value="<?php echo e(old('current_period_end', $periodEnd)); ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                        </div>
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-600 hover:text-white">Update Subscription</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.central', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views\tenants\show.blade.php ENDPATH**/ ?>