

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
                            <th>Tenant ID</th>
                            <th>Tenant</th>
                            <th>Address</th>
                            <th>Domain</th>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Plan Start</th>
                            <th>Plan End</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($tenant->tenant_id); ?></td>
                                <td><?php echo e($tenant->business_name); ?></td>
                                <td><?php echo e(is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : $tenant->business_address); ?></td>
                                <td><?php echo e($tenant->domain); ?></td>
                                <td><?php echo e($tenant->admin_name ?? '—'); ?></td>
                                <td><?php echo e($tenant->admin_email ?? $tenant->business_email); ?></td>
                                <td><?php echo e(ucfirst($tenant->plan ?? 'basic')); ?></td>
                                <td><?php echo e(ucfirst($tenant->status ?? 'active')); ?></td>
                                <td><?php echo e(optional($tenant->plan_started_at)->format('Y-m-d') ?? '—'); ?></td>
                                <td><?php echo e(optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—'); ?></td>
                                <td>
                                    <a href="/tenant/<?php echo e($tenant->tenant_id); ?>" class="btn btn-sm btn-outline-primary">Customize</a>
                                    <?php if(($tenant->status ?? 'active') === 'active'): ?>
                                        <form method="POST" action="<?php echo e(route('tenants.updateStatus', $tenant->tenant_id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="suspended">
                                            <input type="hidden" name="payment_status" value="overdue">
                                            <input type="hidden" name="suspended_message" value="Please contact your administrator.">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?php echo e(route('tenants.updateStatus', $tenant->tenant_id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="active">
                                            <input type="hidden" name="payment_status" value="paid">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Enable</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">No tenants found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.central', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/tenants/index.blade.php ENDPATH**/ ?>