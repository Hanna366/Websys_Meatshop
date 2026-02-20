

<?php $__env->startSection('title', 'Inventory - Meat Shop POS'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Inventory Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <?php if(session('permissions.max_products') == -1 || session('permissions.max_products') > 30): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="showAddStockModal()">
                    <i class="fas fa-plus me-1"></i> Add Stock
                </button>
                <?php else: ?>
                <button type="button" class="btn btn-sm btn-primary" disabled title="Stock management requires Standard plan or higher.">
                    <i class="fas fa-plus me-1"></i> Add Stock
                </button>
                <?php endif; ?>
                
                <?php if(session('permissions.data_export')): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportInventory()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <?php else: ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Export requires Standard plan or higher.">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <?php endif; ?>
            </div>
            <?php if(session('permissions.max_products') != -1 && session('permissions.max_products') <= 30): ?>
            <div class="alert alert-warning mb-0">
                <small><i class="fas fa-exclamation-triangle me-1"></i>
                Advanced inventory management requires Standard plan. <a href="/pricing" class="alert-link">Upgrade now</a>.</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inventory Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">32</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">In Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">27</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱45,680</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Current Stock Levels</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Current Stock (kg)</th>
                            <th>Min. Level (kg)</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Prime Rib Steak</td>
                            <td>45.5</td>
                            <td>20</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Ribeye</td>
                            <td>18.2</td>
                            <td>15</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Tenderloin</td>
                            <td>8.4</td>
                            <td>10</td>
                            <td><span class="badge bg-warning">Low</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Brisket</td>
                            <td>67.8</td>
                            <td>25</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Chuck Roll</td>
                            <td>34.2</td>
                            <td>20</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Short Plate</td>
                            <td>12.6</td>
                            <td>15</td>
                            <td><span class="badge bg-warning">Low</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Neck Bones</td>
                            <td>89.3</td>
                            <td>30</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Soup Bones</td>
                            <td>156.7</td>
                            <td>50</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Fats</td>
                            <td>234.5</td>
                            <td>20</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>2024-02-20 08:00</td>
                            <td>
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Inventory Management Functions
function showAddStockModal() {
    showNotification('Opening add stock form...', 'info');
    // In real app, this would open a modal with product selection
}

function editStock(productName) {
    showNotification('Editing stock for: ' + productName, 'info');
    // In real app, this would open a modal with stock details
}

function addStock(productName) {
    showNotification('Adding stock for: ' + productName, 'info');
    // In real app, this would open a modal to add quantity
}

function exportInventory() {
    const inventory = [
        { product: 'Prime Rib Steak', category: 'Beef', stock: 45, unit: 'kg', status: 'In Stock', lastUpdated: '2024-02-20 10:30' },
        { product: 'Ribeye', category: 'Beef', stock: 32, unit: 'kg', status: 'In Stock', lastUpdated: '2024-02-20 09:15' },
        { product: 'Tenderloin', category: 'Beef', stock: 12, unit: 'kg', status: 'Low Stock', lastUpdated: '2024-02-20 08:00' }
    ];
    
    const dataStr = JSON.stringify(inventory, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = 'inventory_export_' + new Date().toISOString().split('T')[0] + '.json';
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showNotification('Inventory data exported successfully!', 'success');
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/inventory.blade.php ENDPATH**/ ?>