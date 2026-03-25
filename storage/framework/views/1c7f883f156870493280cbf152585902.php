

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-1 text-2xl font-semibold text-slate-900">Tenants</h2>
                <p class="mb-0 text-sm text-slate-500">Centralized control for all branches, plans, and billing states.</p>
            </div>
            <a href="/account/create" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create New Tenant
            </a>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-col gap-3 border-b border-slate-200/70 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <h3 class="heading-font mb-0 text-lg font-semibold text-slate-900">Tenant Directory</h3>
            <form method="GET" action="<?php echo e(route('tenants.index')); ?>" class="flex flex-col gap-2 md:flex-row md:items-center">
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="h-10 w-72 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" placeholder="Search by tenant, domain, or plan">
                </div>
                <select name="status" class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                    <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>>Suspended</option>
                    <option value="unpaid" <?php echo e(request('status') === 'unpaid' ? 'selected' : ''); ?>>Unpaid</option>
                </select>
                <select name="plan" class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Plans</option>
                    <option value="basic" <?php echo e(request('plan') === 'basic' ? 'selected' : ''); ?>>Basic</option>
                    <option value="standard" <?php echo e(request('plan') === 'standard' ? 'selected' : ''); ?>>Standard</option>
                    <option value="premium" <?php echo e(request('plan') === 'premium' ? 'selected' : ''); ?>>Premium</option>
                    <option value="enterprise" <?php echo e(request('plan') === 'enterprise' ? 'selected' : ''); ?>>Enterprise</option>
                </select>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl border border-indigo-200 px-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-600 hover:text-white">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm" id="tenantDirectoryTable">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Tenant ID</th>
                        <th class="px-5 py-3.5 font-semibold">Tenant</th>
                        <th class="px-5 py-3.5 font-semibold">Address</th>
                        <th class="px-5 py-3.5 font-semibold">Domain</th>
                        <th class="px-5 py-3.5 font-semibold">Admin</th>
                        <th class="px-5 py-3.5 font-semibold">Email</th>
                        <th class="px-5 py-3.5 font-semibold">Plan</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold">Plan Start</th>
                        <th class="px-5 py-3.5 font-semibold">Plan End</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="tenant-directory-row transition duration-150 hover:bg-indigo-50/40">
                            <td class="px-5 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($tenant->tenant_id); ?></td>
                            <td class="px-5 py-4 font-medium text-slate-900"><?php echo e($tenant->business_name); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e(is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : $tenant->business_address); ?></td>
                            <td class="px-5 py-4">
                                <?php if(!empty($tenant->domain)): ?>
                                    <?php
                                        $rawDomain = trim((string) $tenant->domain);
                                        $normalizedDomain = preg_replace('#^https?://#i', '', $rawDomain);
                                        $normalizedDomain = rtrim($normalizedDomain, '/');
                                        $normalizedDomain = str_ireplace('locasthost', 'localhost', $normalizedDomain);
                                        $scheme = request()->isSecure() ? 'https' : 'http';
                                        $hasPort = preg_match('/:\\d+$/', $normalizedDomain) === 1;
                                        $tenantPort = app()->environment('local') && !$hasPort ? ':8000' : '';
                                        $tenantUrl = $scheme . '://' . $normalizedDomain . $tenantPort . '/login?force_login=1';
                                    ?>
                                    <a href="<?php echo e($tenantUrl); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                                        <?php echo e($normalizedDomain); ?>

                                        <i data-lucide="external-link" class="h-3.5 w-3.5"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4"><?php echo e($tenant->admin_name ?? '—'); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e($tenant->admin_email ?? $tenant->business_email); ?></td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700"><?php echo e(ucfirst($tenant->plan ?? 'basic')); ?></span>
                            </td>
                            <td class="px-5 py-4">
                                <?php
                                    $status = strtolower((string) ($tenant->status ?? 'active'));
                                    $statusClasses = [
                                        'active' => 'bg-emerald-50 text-emerald-700',
                                        'suspended' => 'bg-amber-50 text-amber-700',
                                        'unpaid' => 'bg-rose-50 text-rose-700',
                                        'inactive' => 'bg-slate-100 text-slate-700',
                                    ][$status] ?? 'bg-slate-100 text-slate-700';
                                ?>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e(ucfirst($tenant->status ?? 'active')); ?></span>
                            </td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e(optional($tenant->plan_started_at)->format('Y-m-d') ?? '—'); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo e(optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—'); ?></td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="/tenant/<?php echo e($tenant->tenant_id); ?>" class="inline-flex items-center gap-1 rounded-xl border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-600 hover:text-white">
                                        Customize
                                        <i data-lucide="settings-2" class="h-3.5 w-3.5"></i>
                                    </a>
                                    <?php if(!empty($tenant->domain)): ?>
                                        <?php
                                            $rawDomain = trim((string) $tenant->domain);
                                            $normalizedDomain = preg_replace('#^https?://#i', '', $rawDomain);
                                            $normalizedDomain = rtrim($normalizedDomain, '/');
                                            $normalizedDomain = str_ireplace('locasthost', 'localhost', $normalizedDomain);
                                            $scheme = request()->isSecure() ? 'https' : 'http';
                                            $hasPort = preg_match('/:\\d+$/', $normalizedDomain) === 1;
                                            $tenantPort = app()->environment('local') && !$hasPort ? ':8000' : '';
                                            $tenantUrl = $scheme . '://' . $normalizedDomain . $tenantPort . '/login?force_login=1';
                                        ?>
                                        <a href="<?php echo e($tenantUrl); ?>" class="inline-flex items-center gap-1 rounded-xl border border-teal-200 px-3 py-2 text-xs font-semibold text-teal-700 transition hover:-translate-y-0.5 hover:border-teal-300 hover:bg-teal-600 hover:text-white" target="_blank" rel="noopener noreferrer">
                                            Open Tenant
                                            <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if(($tenant->status ?? 'active') === 'active'): ?>
                                        <form method="POST" action="<?php echo e(route('tenants.updateStatus', $tenant->tenant_id)); ?>" class="m-0 inline-flex">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="suspended">
                                            <input type="hidden" name="payment_status" value="overdue">
                                            <input type="hidden" name="suspended_message" value="Please contact your administrator.">
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:-translate-y-0.5 hover:border-rose-300 hover:bg-rose-600 hover:text-white">
                                                Disable
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?php echo e(route('tenants.updateStatus', $tenant->tenant_id)); ?>" class="m-0 inline-flex">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="active">
                                            <input type="hidden" name="payment_status" value="paid">
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-600 hover:text-white">
                                                Enable
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="px-5 py-10 text-center text-sm text-slate-500">No tenants found.</td>
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
    // Tenant filtering is performed server-side via query params.
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.central', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views\tenants\index.blade.php ENDPATH**/ ?>