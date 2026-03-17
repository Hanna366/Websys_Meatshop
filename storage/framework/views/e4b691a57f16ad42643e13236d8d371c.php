

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Tenant Details</h1>
        <a href="/tenants" class="btn btn-secondary">Back to list</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Basic Info</h5>
                    <p><strong>Name:</strong> <?php echo e($tenant->business_name); ?></p>
                    <p><strong>Domain:</strong> <?php echo e($tenant->domain); ?></p>
                    <p><strong>Plan:</strong> <?php echo e(ucfirst($tenant->plan)); ?></p>
                    <p><strong>Plan Start:</strong> <?php echo e(optional($tenant->plan_started_at)->format('Y-m-d') ?? '—'); ?></p>
                    <p><strong>Plan End:</strong> <?php echo e(optional($tenant->plan_ends_at)->format('Y-m-d') ?? '—'); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Access Info</h5>
                    <p><strong>Admin:</strong> <?php echo e($tenant->admin_name ?? '—'); ?></p>
                    <p><strong>Email:</strong> <?php echo e($tenant->admin_email ?? $tenant->business_email); ?></p>
                    <p><strong>Tenant DB:</strong> <?php echo e($tenant->db_name); ?></p>
                    <p><strong>Tenant Domain:</strong> <code><?php echo e($tenant->domain); ?></code></p>

                    <div class="alert alert-info mt-3">
                        <h6>Localhost Setup</h6>
                        <p>To access this tenant locally, add a hosts entry such as:</p>
                        <pre>127.0.0.1 <?php echo e($tenant->domain); ?></pre>
                        <p>Then visit <strong>http://<?php echo e($tenant->domain); ?>:8000</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views\tenants\show.blade.php ENDPATH**/ ?>