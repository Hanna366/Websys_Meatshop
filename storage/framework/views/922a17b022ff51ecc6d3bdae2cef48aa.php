

<?php $__env->startSection('title', 'MeatShop Central'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">MeatShop Central</h1>
        <a href="<?php echo e(route('tenants.create')); ?>" class="btn btn-primary">Create Tenant</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Tenants</p>
                    <h3 class="mb-0"><?php echo e($stats['total_tenants'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
                <div class="card-body">
                    <p class="text-muted mb-1">Active Tenants</p>
                    <h3 class="mb-0 text-success"><?php echo e($stats['active_tenants'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <p class="text-muted mb-1">Suspended Tenants</p>
                    <h3 class="mb-0 text-warning"><?php echo e($stats['suspended_tenants'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <p class="text-muted mb-1">Unpaid Tenants</p>
                    <h3 class="mb-0 text-danger"><?php echo e($stats['unpaid_tenants'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Tenant Table</h5>
                <a href="<?php echo e(route('tenants.index')); ?>" class="btn btn-outline-primary btn-sm">Open Full Table</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Domain</th>
                            <th>Address</th>
                            <th>Administrator</th>
                            <th>Admin Email</th>
                            <th>Pricing Model</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($tenant->business_name); ?></td>
                                <td><?php echo e($tenant->domain ?? '—'); ?></td>
                                <td><?php echo e(is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : ($tenant->business_address ?: '—')); ?></td>
                                <td><?php echo e($tenant->admin_name ?? '—'); ?></td>
                                <td><?php echo e($tenant->admin_email ?? $tenant->business_email); ?></td>
                                <td><?php echo e(ucfirst($tenant->plan ?? 'basic')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('tenants.show', $tenant->tenant_id)); ?>" class="btn btn-sm btn-outline-primary">Customize</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No tenants yet. Create your first tenant.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="mb-1"><strong>Tenant host format:</strong> ramcar.localhost:8000</p>
            <p class="mb-0 text-muted">Run: php artisan serve --host=127.0.0.1 --port=8000</p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.central', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/central/home.blade.php ENDPATH**/ ?>