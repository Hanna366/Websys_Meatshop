

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">Dashboard</h1>
            <?php if(session('user')): ?>
                <div class="alert alert-info mb-2">
                    <i class="fas fa-user me-2"></i>
                    Welcome back, <strong><?php echo e(session('user.name')); ?></strong>! | 
                    Email: <strong><?php echo e(session('user.email')); ?></strong> | 
                    Plan: <span class="badge bg-<?php echo e(session('user.plan') == 'Premium' ? 'danger' : (session('user.plan') == 'Standard' ? 'warning' : 'primary')); ?> text-white">
                        <?php echo e(session('user.plan')); ?>

                    </span>
                </div>
            <?php endif; ?>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportDashboard()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printDashboard()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
            </div>
            <?php if(session('user')): ?>
                <a href="/pricing" class="btn btn-sm btn-danger">
                    <i class="fas fa-crown me-1"></i>
                    Upgrade Plan
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$12,456</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">245</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,234</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Sales</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#">View All</a>
                    <a class="dropdown-item" href="#">Export Data</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">New Sale</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-001</td>
                            <td>John Smith</td>
                            <td>Beef, Chicken, Pork</td>
                            <td>$156.78</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>2024-02-20</td>
                        </tr>
                        <tr>
                            <td>#ORD-002</td>
                            <td>Sarah Johnson</td>
                            <td>Lamb, Turkey</td>
                            <td>$89.45</td>
                            <td><span class="badge bg-warning">Processing</span></td>
                            <td>2024-02-20</td>
                        </tr>
                        <tr>
                            <td>#ORD-003</td>
                            <td>Mike Wilson</td>
                            <td>Beef, Pork</td>
                            <td>$234.12</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>2024-02-19</td>
                        </tr>
                        <tr>
                            <td>#ORD-004</td>
                            <td>Emily Davis</td>
                            <td>Chicken, Fish</td>
                            <td>$67.89</td>
                            <td><span class="badge bg-info">Pending</span></td>
                            <td>2024-02-19</td>
                        </tr>
                        <tr>
                            <td>#ORD-005</td>
                            <td>Robert Brown</td>
                            <td>Beef, Lamb, Turkey</td>
                            <td>$312.45</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>2024-02-18</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Product Categories</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales',
                data: [1200, 1900, 3000, 5000, 2000, 3000, 4500],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Beef', 'Chicken', 'Pork', 'Lamb', 'Turkey', 'Fish'],
            datasets: [{
                data: [30, 25, 20, 10, 10, 5],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        }
    });

    // Export Dashboard Function
    function exportDashboard() {
        const data = {
            user: <?php if(session('user')): ?> {
                email: '<?php echo e(session('user.email')); ?>',
                plan: '<?php echo e(session('user.plan')); ?>'
            } <?php else: ?> null <?php endif; ?>,
            timestamp: new Date().toISOString(),
            stats: {
                todaySales: '₱12,456',
                products: 245,
                customers: 128,
                orders: 89
            }
        };
        
        const dataStr = JSON.stringify(data, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
        
        const exportFileDefaultName = 'dashboard_export_' + new Date().toISOString().split('T')[0] + '.json';
        
        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
        
        // Show success message
        showNotification('Dashboard data exported successfully!', 'success');
    }

    // Print Dashboard Function
    function printDashboard() {
        window.print();
        showNotification('Print dialog opened', 'info');
    }

    // Show notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            ${message}
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/dashboard/index.blade.php ENDPATH**/ ?>