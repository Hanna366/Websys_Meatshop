

<?php $__env->startSection('title', 'Sales - Meat Shop POS'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Sales Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> New Sale
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Sales Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today's Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱12,450</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">This Week</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱87,320</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱345,680</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">247</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Recent Sales</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#S001</td>
                            <td>John Martinez</td>
                            <td>3 items</td>
                            <td class="text-end fw-bold">₱8,450</td>
                            <td><span class="badge bg-success">Cash</span></td>
                            <td>2024-02-20 14:30</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#S002</td>
                            <td>Maria Santos</td>
                            <td>5 items</td>
                            <td class="text-end fw-bold">₱12,340</td>
                            <td><span class="badge bg-info">Card</span></td>
                            <td>2024-02-20 13:45</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#S003</td>
                            <td>Roberto Cruz</td>
                            <td>2 items</td>
                            <td class="text-end fw-bold">₱5,680</td>
                            <td><span class="badge bg-warning">GCash</span></td>
                            <td>2024-02-20 12:20</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#S004</td>
                            <td>Linda Reyes</td>
                            <td>4 items</td>
                            <td class="text-end fw-bold">₱9,230</td>
                            <td><span class="badge bg-success">Cash</span></td>
                            <td>2024-02-20 11:15</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#S005</td>
                            <td>Carlos Mendoza</td>
                            <td>6 items</td>
                            <td class="text-end fw-bold">₱15,890</td>
                            <td><span class="badge bg-info">Card</span></td>
                            <td>2024-02-20 10:30</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/sales.blade.php ENDPATH**/ ?>