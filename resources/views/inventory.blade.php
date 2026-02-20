@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Inventory Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportInventory()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
                <i class="fas fa-exchange-alt me-1"></i> Stock Adjustment
            </button>
        </div>
    </div>

    <!-- Inventory Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Stock Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$45,678.90</div>
                            <div class="text-xs text-gray-500">Current inventory value</div>
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
                                Total Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,234 kg</div>
                            <div class="text-xs text-gray-500">Across all products</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-weight fa-2x text-gray-300"></i>
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
                                Low Stock Alert</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                            <div class="text-xs text-gray-500">Items need restocking</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Expiring Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                            <div class="text-xs text-gray-500">Within 7 days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert</h6>
        <p class="mb-0">The following items are running low and need to be restocked soon:</p>
        <ul class="mb-0 mt-2">
            <li>Pork Chops - 5 kg remaining (min: 10 kg)</li>
            <li>Lamb Leg - 8 kg remaining (min: 15 kg)</li>
            <li>Chicken Wings - 3 kg remaining (min: 10 kg)</li>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Inventory Overview</h6>
            <div class="d-flex align-items-center">
                <div class="input-group me-2" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Search inventory..." id="inventorySearch">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="inventoryFilter" data-bs-toggle="dropdown">
                        <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="#" onclick="filterInventory('all')">All Items</a>
                        <a class="dropdown-item" href="#" onclick="filterInventory('lowstock')">Low Stock</a>
                        <a class="dropdown-item" href="#" onclick="filterInventory('outofstock')">Out of Stock</a>
                        <a class="dropdown-item" href="#" onclick="filterInventory('expiring')">Expiring Soon</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="filterInventory('beef')">Beef</a>
                        <a class="dropdown-item" href="#" onclick="filterInventory('chicken')">Chicken</a>
                        <a class="dropdown-item" href="#" onclick="filterInventory('pork')">Pork</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Min Stock Level</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Last Updated</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Premium Beef Steak</td>
                            <td><span class="badge bg-primary">Beef</span></td>
                            <td>45 kg</td>
                            <td>10 kg</td>
                            <td>$12.50/kg</td>
                            <td>$562.50</td>
                            <td>2024-02-20</td>
                            <td>2024-02-28</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adjustStock(1)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addStock(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Fresh Chicken Breast</td>
                            <td><span class="badge bg-info">Chicken</span></td>
                            <td>32 kg</td>
                            <td>10 kg</td>
                            <td>$8.75/kg</td>
                            <td>$280.00</td>
                            <td>2024-02-20</td>
                            <td>2024-02-25</td>
                            <td><span class="badge bg-warning">Expiring Soon</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adjustStock(2)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addStock(2)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Pork Chops</td>
                            <td><span class="badge bg-warning">Pork</span></td>
                            <td>5 kg</td>
                            <td>10 kg</td>
                            <td>$10.25/kg</td>
                            <td>$51.25</td>
                            <td>2024-02-20</td>
                            <td>2024-03-05</td>
                            <td><span class="badge bg-danger">Low Stock</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adjustStock(3)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addStock(3)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Lamb Leg</td>
                            <td><span class="badge bg-secondary">Lamb</span></td>
                            <td>8 kg</td>
                            <td>15 kg</td>
                            <td>$18.50/kg</td>
                            <td>$148.00</td>
                            <td>2024-02-19</td>
                            <td>2024-03-10</td>
                            <td><span class="badge bg-danger">Low Stock</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adjustStock(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addStock(4)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Turkey Breast</td>
                            <td><span class="badge bg-dark">Turkey</span></td>
                            <td>22 kg</td>
                            <td>8 kg</td>
                            <td>$14.75/kg</td>
                            <td>$324.50</td>
                            <td>2024-02-20</td>
                            <td>2024-03-15</td>
                            <td><span class="badge bg-success">Good</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adjustStock(5)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addStock(5)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Salmon Fillet</td>
                            <td><span class="badge bg-info">Fish</span></td>
                            <td>0 kg</td>
                            <td>5 kg</td>
                            <td>$22.00/kg</td>
                            <td>$0.00</td>
                            <td>2024-02-18</td>
                            <td>-</td>
                            <td><span class="badge bg-danger">Out of Stock</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adjustStock(6)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="restockItem(6)">
                                        <i class="fas fa-box"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Movement History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Stock Movements</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="movementFilter" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#">View All</a>
                    <a class="dropdown-item" href="#">Export History</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Add New Movement</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Movement Type</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2024-02-20 08:30 AM</td>
                            <td>Premium Beef Steak</td>
                            <td><span class="badge bg-success">Stock In</span></td>
                            <td>+20 kg</td>
                            <td>New delivery from supplier</td>
                            <td>John Smith</td>
                        </tr>
                        <tr>
                            <td>2024-02-20 09:15 AM</td>
                            <td>Fresh Chicken Breast</td>
                            <td><span class="badge bg-danger">Stock Out</span></td>
                            <td>-5 kg</td>
                            <td>Sale #ORD-001</td>
                            <td>Sarah Johnson</td>
                        </tr>
                        <tr>
                            <td>2024-02-20 10:45 AM</td>
                            <td>Pork Chops</td>
                            <td><span class="badge bg-warning">Adjustment</span></td>
                            <td>-2 kg</td>
                            <td>Quality control - damaged items</td>
                            <td>Mike Wilson</td>
                        </tr>
                        <tr>
                            <td>2024-02-20 11:30 AM</td>
                            <td>Lamb Leg</td>
                            <td><span class="badge bg-danger">Stock Out</span></td>
                            <td>-3 kg</td>
                            <td>Sale #ORD-003</td>
                            <td>Emily Davis</td>
                        </tr>
                        <tr>
                            <td>2024-02-20 02:15 PM</td>
                            <td>Salmon Fillet</td>
                            <td><span class="badge bg-warning">Adjustment</span></td>
                            <td>-8 kg</td>
                            <td>Expired items removed</td>
                            <td>Robert Brown</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-labelledby="stockAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockAdjustmentModalLabel">Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stockAdjustmentForm">
                    <div class="mb-3">
                        <label for="productSelect" class="form-label">Select Product</label>
                        <select class="form-select" id="productSelect" required>
                            <option value="">Choose a product...</option>
                            <option value="1">Premium Beef Steak</option>
                            <option value="2">Fresh Chicken Breast</option>
                            <option value="3">Pork Chops</option>
                            <option value="4">Lamb Leg</option>
                            <option value="5">Turkey Breast</option>
                            <option value="6">Salmon Fillet</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adjustmentType" class="form-label">Adjustment Type</label>
                        <select class="form-select" id="adjustmentType" required>
                            <option value="stockin">Stock In</option>
                            <option value="stockout">Stock Out</option>
                            <option value="adjustment">Manual Adjustment</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity (kg)</label>
                        <input type="number" class="form-control" id="quantity" step="0.1" min="0.1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="currentStock" class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="currentStock" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newStock" class="form-label">New Stock Level</label>
                        <input type="text" class="form-control" id="newStock" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processStockAdjustment()">Process Adjustment</button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('inventorySearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#inventoryTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter inventory
function filterInventory(filter) {
    console.log('Filtering inventory by:', filter);
    // Implement filter logic here
}

// Stock adjustment functions
function adjustStock(id) {
    console.log('Adjusting stock for product:', id);
    // Open stock adjustment modal with product pre-selected
}

function addStock(id) {
    console.log('Adding stock for product:', id);
    // Open stock adjustment modal with stock in pre-selected
}

function restockItem(id) {
    console.log('Restocking item:', id);
    // Open stock adjustment modal for out of stock item
}

// Stock adjustment modal functions
document.getElementById('productSelect').addEventListener('change', function() {
    const productId = this.value;
    if(productId) {
        // Fetch current stock for selected product
        const currentStock = getCurrentStock(productId);
        document.getElementById('currentStock').value = currentStock + ' kg';
        updateNewStock();
    }
});

document.getElementById('adjustmentType').addEventListener('change', updateNewStock);
document.getElementById('quantity').addEventListener('input', updateNewStock);

function getCurrentStock(productId) {
    // This would fetch from database in real implementation
    const stocks = {1: 45, 2: 32, 3: 5, 4: 8, 5: 22, 6: 0};
    return stocks[productId] || 0;
}

function updateNewStock() {
    const currentStock = parseFloat(document.getElementById('currentStock').value) || 0;
    const adjustmentType = document.getElementById('adjustmentType').value;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    let newStock = currentStock;
    if(adjustmentType === 'stockin') {
        newStock = currentStock + quantity;
    } else if(adjustmentType === 'stockout') {
        newStock = Math.max(0, currentStock - quantity);
    } else if(adjustmentType === 'adjustment') {
        newStock = quantity;
    }
    
    document.getElementById('newStock').value = newStock.toFixed(1) + ' kg';
}

function processStockAdjustment() {
    const form = document.getElementById('stockAdjustmentForm');
    if(form.checkValidity()) {
        console.log('Processing stock adjustment');
        // Implement stock adjustment logic
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal'));
        modal.hide();
        form.reset();
        document.getElementById('currentStock').value = '';
        document.getElementById('newStock').value = '';
    } else {
        form.reportValidity();
    }
}

function exportInventory() {
    console.log('Exporting inventory data');
    // Implement export functionality
}
</script>
@endsection
