

<?php $__env->startSection('title', 'MeatShop Central'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Database Connection Error Alert -->
    <?php if(isset($xampp_status['error']) && $xampp_status['error']): ?>
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i data-lucide="alert-circle" class="h-5 w-5 text-rose-600"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-rose-800">Database Connection Issue</h3>
                <p class="mt-1 text-sm text-rose-700"><?php echo e($xampp_status['error']); ?></p>
                <div class="mt-2 text-xs text-rose-600">
                    <strong>Solutions:</strong>
                    <ul class="mt-1 list-disc list-inside space-y-1">
                        <li>Make sure XAMPP MySQL service is running</li>
                        <li>Check your .env file database configuration</li>
                        <li>Verify MySQL username and password</li>
                        <li>Ensure MySQL is running on port <?php echo e(env('DB_PORT', '3306')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Database Health Overview -->
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <!-- XAMPP MySQL Status -->
        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">XAMPP MySQL</p>
                    <h3 class="heading-font text-2xl font-semibold <?php echo e($xampp_status['is_running'] ? 'emerald' : 'rose'); ?>-700"><?php echo e($xampp_status['is_running'] ? '✓' : '✗'); ?></h3>
                    <p class="mt-1 text-xs text-<?php echo e($xampp_status['is_running'] ? 'emerald' : 'rose'); ?>-600">
                        <?php echo e($xampp_status['is_running'] ? 'Running' : 'Stopped'); ?>

                        <?php if($xampp_status['version']): ?>
                            <br>v<?php echo e(substr($xampp_status['version'], 0, 6)); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-<?php echo e($xampp_status['is_running'] ? 'emerald' : 'rose'); ?>-50 text-<?php echo e($xampp_status['is_running'] ? 'emerald' : 'rose'); ?>-700">
                    <i data-lucide="server" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-<?php echo e($xampp_status['is_running'] ? 'emerald' : 'rose'); ?>-500/60 to-<?php echo e($xampp_status['is_running'] ? 'emerald' : 'rose'); ?>-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Database Health</p>
                    <h3 class="heading-font text-2xl font-semibold text-<?php echo e($database_health['color']); ?>-700"><?php echo e($database_health['status'] === 'healthy' ? '✓' : ($database_health['status'] === 'warning' ? '!' : '✗')); ?></h3>
                    <p class="mt-1 text-xs text-<?php echo e($database_health['color']); ?>-600"><?php echo e($database_health['status']); ?></p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-<?php echo e($database_health['color']); ?>-50 text-<?php echo e($database_health['color']); ?>-700">
                    <i data-lucide="activity" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-<?php echo e($database_health['color']); ?>-500/60 to-<?php echo e($database_health['color']); ?>-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Available</p>
                    <h3 class="heading-font text-2xl font-semibold text-emerald-700"><?php echo e($database_summary['available_tenants']); ?></h3>
                    <p class="mt-1 text-xs text-slate-500">of <?php echo e($database_summary['total_tenants']); ?></p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <i data-lucide="check-circle" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-emerald-500/60 to-emerald-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Total Size</p>
                    <h3 class="heading-font text-2xl font-semibold text-indigo-700"><?php echo e($database_summary['total_database_size']); ?></h3>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e($database_summary['total_tables']); ?> tables</p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                    <i data-lucide="hard-drive" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-indigo-500/60 to-indigo-100"></div>
        </article>
    </section>

    <!-- Tenant Statistics -->
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Total Tenants</p>
                    <h3 class="heading-font text-2xl font-semibold text-slate-900"><?php echo e($stats['total_tenants'] ?? 0); ?></h3>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                    <i data-lucide="users" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-indigo-500/60 to-indigo-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Active Tenants</p>
                    <h3 class="heading-font text-2xl font-semibold text-emerald-700"><?php echo e($stats['active_tenants'] ?? 0); ?></h3>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <i data-lucide="badge-check" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-emerald-500/60 to-emerald-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Suspended Tenants</p>
                    <h3 class="heading-font text-2xl font-semibold text-amber-600"><?php echo e($stats['suspended_tenants'] ?? 0); ?></h3>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <i data-lucide="shield-alert" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-amber-500/60 to-amber-100"></div>
        </article>

        <article class="group rounded-xl border border-slate-200/70 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="mb-1 text-xs font-medium text-slate-500">Unpaid Tenants</p>
                    <h3 class="heading-font text-2xl font-semibold text-rose-600"><?php echo e($stats['unpaid_tenants'] ?? 0); ?></h3>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-700">
                    <i data-lucide="wallet-cards" class="h-4 w-4"></i>
                </span>
            </div>
            <div class="mt-3 h-1 rounded-full bg-gradient-to-r from-rose-500/60 to-rose-100"></div>
        </article>
    </section>

    <!-- Tenant Table with Database Monitoring -->
    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-0 text-lg font-semibold text-slate-900">Tenant Table</h2>
                <p class="mb-0 text-sm text-slate-500">Manage tenant access, plans, and monitor database health.</p>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input id="tenantSearch" type="text" class="h-10 w-72 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" placeholder="Search tenants...">
                </div>
                <a href="<?php echo e(route('tenants.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <i data-lucide="table" class="h-4 w-4"></i>
                    Open Full Table
                </a>
            </div>
        </div>

        <div class="overflow-auto h-[360px] lg:h-[460px]">
            <table class="min-w-[1100px] table-auto text-sm" id="tenantTable">
            <thead class="sticky top-0 z-10 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-[18%] px-5 py-3.5 font-semibold">Name</th>
                        <th class="w-[24%] px-5 py-3.5 font-semibold">Domain</th>
                        <th class="w-[16%] px-5 py-3.5 font-semibold">Database Status</th>
                        <th class="w-[14%] px-5 py-3.5 font-semibold">Database Size</th>
                        <th class="w-[8%] px-5 py-3.5 font-semibold">Tables</th>
                        <th class="w-[12%] px-5 py-3.5 font-semibold">Administrator</th>
                        <th class="w-[8%] px-5 py-3.5 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $dbInfo = $database_stats[$tenant->tenant_id] ?? null;
                            $isAvailable = $dbInfo['is_available'] ?? false;
                            $statusColor = $isAvailable ? 'emerald' : 'rose';
                            $statusIcon = $isAvailable ? 'check-circle' : 'x-circle';
                            $statusText = $isAvailable ? 'Available' : 'Unavailable';
                        ?>
                        <tr class="tenant-row transition duration-150 hover:bg-indigo-50/40">
                            <td class="max-w-[220px] px-5 py-4 font-medium text-slate-900">
                                <span class="block truncate" title="<?php echo e($tenant->business_name); ?>"><?php echo e($tenant->business_name); ?></span>
                            </td>
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
                                    <a href="<?php echo e($tenantUrl); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex max-w-[200px] items-center gap-1 rounded-lg px-2 py-1 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100" title="<?php echo e($normalizedDomain); ?>">
                                        <span class="truncate"><?php echo e($normalizedDomain); ?></span>
                                        <i data-lucide="external-link" class="h-3.5 w-3.5"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="<?php echo e($statusIcon); ?>" class="h-4 w-4 text-<?php echo e($statusColor); ?>-600"></i>
                                    <span class="inline-flex items-center rounded-full bg-<?php echo e($statusColor); ?>-50 px-2 py-1 text-xs font-medium text-<?php echo e($statusColor); ?>-700">
                                        <?php echo e($statusText); ?>

                                    </span>
                                </div>
                                <?php if(isset($dbInfo['error'])): ?>
                                    <p class="mt-1 text-xs text-rose-600 truncate" title="<?php echo e($dbInfo['error']); ?>">
                                        <?php echo e(Str::limit($dbInfo['error'], 50)); ?>

                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-medium text-slate-700"><?php echo e($dbInfo['database_size'] ?? 'N/A'); ?></span>
                                <?php if($dbInfo['largest_table'] && $dbInfo['largest_table'] !== 'N/A'): ?>
                                    <p class="mt-1 text-xs text-slate-500 truncate" title="<?php echo e($dbInfo['largest_table']); ?>">
                                        <?php echo e($dbInfo['largest_table']); ?>

                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-medium text-slate-700"><?php echo e($dbInfo['table_count'] ?? 0); ?></span>
                            </td>
                            <td class="max-w-[180px] px-5 py-4">
                                <span class="block truncate" title="<?php echo e($tenant->admin_name ?? '—'); ?>"><?php echo e($tenant->admin_name ?? '—'); ?></span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="<?php echo e(route('tenants.show', $tenant->tenant_id)); ?>" class="inline-flex items-center gap-1 rounded-xl border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-600 hover:text-white">
                                    Open Tenant
                                    <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No tenants yet. Create your first tenant.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-5 py-4 text-sm text-slate-600">
        <p class="mb-1 font-medium text-slate-700">Tenant host format: <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">ramcar.localhost:8000</span></p>
        <p class="mb-0">Run: <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">php artisan serve --host=127.0.0.1 --port=8000</span></p>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.getElementById('tenantSearch')?.addEventListener('input', function (event) {
        const query = event.target.value.trim().toLowerCase();
        document.querySelectorAll('#tenantTable .tenant-row').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.central', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/central/home.blade.php ENDPATH**/ ?>