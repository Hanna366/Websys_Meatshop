

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tenants</h1>
        <a href="/account/create" class="btn btn-primary">Create New Tenant</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Address</th>
                            <th>Domain</th>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Plan Start</th>
                            <th>Plan End</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($tenant->business_name); ?></td>
                                <td><?php echo e(is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : $tenant->business_address); ?></td>
                                <td><?php echo e($tenant->domain); ?></td>
                                <td><?php echo e($tenant->admin_name ?? '—'); ?></td>
                                <td><?php echo e($tenant->admin_email ?? $tenant->business_email); ?></td>
                                <td><?php echo e(ucfirst($tenant->plan ?? 'basic')); ?></td>
                                <td><?php echo e(optional($tenant->plan_started_at)->format('Y-m-d') ?? '—'); ?></td>
                                <td><?php echo e(optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—'); ?></td>
                                <td>
                                    <a href="/tenant/<?php echo e($tenant->tenant_id); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views\tenants\index.blade.php ENDPATH**/ ?>