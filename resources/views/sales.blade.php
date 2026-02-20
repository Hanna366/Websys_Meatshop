@extends('layouts.app')

@section('title', 'Sales - Meat Shop POS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Sales Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                @if(session('permissions.pos_access'))
                <button type="button" class="btn btn-sm btn-primary" onclick="showNewSaleModal()">
                    <i class="fas fa-plus me-1"></i> New Sale
                </button>
                @else
                <button type="button" class="btn btn-sm btn-primary" disabled title="POS functionality requires Standard plan or higher.">
                    <i class="fas fa-plus me-1"></i> New Sale
                </button>
                @endif
                
                @if(session('permissions.data_export'))
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportSales()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                @else
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Export requires Standard plan or higher.">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                @endif
            </div>
            @if(!session('permissions.pos_access'))
            <div class="alert alert-warning mb-0">
                <small><i class="fas fa-exclamation-triangle me-1"></i>
                POS functionality requires Standard plan or higher. <a href="/pricing" class="alert-link">Upgrade now</a>.</small>
            </div>
            @endif
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
                                <button class="btn btn-sm btn-info" onclick="viewSale('#S005')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="printSale('#S005')">
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

<script>
// Sales Management Functions
function showNewSaleModal() {
    showNotification('Opening new sale form...', 'info');
    // In real app, this would open a modal with product selection
}

function viewSale(saleId) {
    showNotification('Viewing sale details for ' + saleId, 'info');
    // In real app, this would open a modal with sale details
}

function printSale(saleId) {
    showNotification('Preparing receipt for ' + saleId + '...', 'info');
    window.print();
    // In real app, this would generate a printable receipt
}

function exportSales() {
    const sales = [
        { id: '#S001', customer: 'Juan Santos', items: 3, total: '₱8,450', payment: 'Cash', date: '2024-02-20 14:30', status: 'Completed' },
        { id: '#S002', customer: 'Maria Reyes', items: 5, total: '₱12,890', payment: 'Card', date: '2024-02-20 13:45', status: 'Completed' },
        { id: '#S003', customer: 'Roberto Cruz', items: 2, total: '₱5,670', payment: 'Cash', date: '2024-02-20 12:20', status: 'Completed' },
        { id: '#S004', customer: 'Ana Martinez', items: 8, total: '₱18,340', payment: 'Card', date: '2024-02-20 11:15', status: 'Completed' },
        { id: '#S005', customer: 'Carlos Mendoza', items: 6, total: '₱15,890', payment: 'Card', date: '2024-02-20 10:30', status: 'Completed' }
    ];
    
    const dataStr = JSON.stringify(sales, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = 'sales_export_' + new Date().toISOString().split('T')[0] + '.json';
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showNotification('Sales data exported successfully!', 'success');
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
@endsection
