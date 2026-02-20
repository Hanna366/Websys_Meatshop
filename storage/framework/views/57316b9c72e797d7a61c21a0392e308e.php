<?php $__env->startSection('title', 'Sales'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Sales Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportSalesData()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                <i class="fas fa-plus me-1"></i> New Sale
            </button>
        </div>
    </div>

    <!-- Sales Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$3,456.78</div>
                            <div class="text-xs text-gray-500">+12% from yesterday</div>
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
                                Weekly Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$18,234.56</div>
                            <div class="text-xs text-gray-500">+8% from last week</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">142</div>
                            <div class="text-xs text-gray-500">Today</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                Avg. Sale</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$24.34</div>
                            <div class="text-xs text-gray-500">Per transaction</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Sales</h6>
            <div class="d-flex align-items-center">
                <div class="input-group me-2" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Search sales..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="filterDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="#" onclick="filterSales('all')">All Sales</a>
                        <a class="dropdown-item" href="#" onclick="filterSales('completed')">Completed</a>
                        <a class="dropdown-item" href="#" onclick="filterSales('pending')">Pending</a>
                        <a class="dropdown-item" href="#" onclick="filterSales('cancelled')">Cancelled</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="filterSales('today')">Today</a>
                        <a class="dropdown-item" href="#" onclick="filterSales('week')">This Week</a>
                        <a class="dropdown-item" href="#" onclick="filterSales('month')">This Month</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="salesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-2024-001</td>
                            <td>2024-02-20 09:15 AM</td>
                            <td>John Smith</td>
                            <td>5 items</td>
                            <td>$156.78</td>
                            <td><span class="badge bg-success">Cash</span></td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSale(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReceipt(1)">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-002</td>
                            <td>2024-02-20 10:30 AM</td>
                            <td>Sarah Johnson</td>
                            <td>3 items</td>
                            <td>$89.45</td>
                            <td><span class="badge bg-info">Card</span></td>
                            <td><span class="badge bg-warning">Processing</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSale(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="completeSale(2)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-003</td>
                            <td>2024-02-20 11:45 AM</td>
                            <td>Mike Wilson</td>
                            <td>7 items</td>
                            <td>$234.12</td>
                            <td><span class="badge bg-success">Cash</span></td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSale(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReceipt(3)">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-004</td>
                            <td>2024-02-20 01:20 PM</td>
                            <td>Emily Davis</td>
                            <td>2 items</td>
                            <td>$67.89</td>
                            <td><span class="badge bg-info">Card</span></td>
                            <td><span class="badge bg-info">Pending</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSale(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="editSale(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-2024-005</td>
                            <td>2024-02-20 02:30 PM</td>
                            <td>Robert Brown</td>
                            <td>4 items</td>
                            <td>$312.45</td>
                            <td><span class="badge bg-warning">Mobile</span></td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSale(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReceipt(5)">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav aria-label="Sales pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- New Sale Modal -->
<div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSaleModalLabel">Create New Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newSaleForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customerSelect" class="form-label">Customer</label>
                            <select class="form-select" id="customerSelect">
                                <option value="">Walk-in Customer</option>
                                <option value="1">John Smith</option>
                                <option value="2">Sarah Johnson</option>
                                <option value="3">Mike Wilson</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile Payment</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Add Products</label>
                        <div class="input-group">
                            <select class="form-select" id="productSelect">
                                <option value="">Select Product</option>
                                <option value="1">Beef - $12.50/kg</option>
                                <option value="2">Chicken - $8.75/kg</option>
                                <option value="3">Pork - $10.25/kg</option>
                                <option value="4">Lamb - $18.50/kg</option>
                            </select>
                            <input type="number" class="form-control" placeholder="Quantity" id="quantityInput" min="1" value="1">
                            <button type="button" class="btn btn-outline-primary" onclick="addProduct()">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Order Items</label>
                        <div id="orderItems" class="border rounded p-3 bg-light">
                            <p class="text-muted mb-0">No items added yet</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="discountInput" class="form-label">Discount ($)</label>
                            <input type="number" class="form-control" id="discountInput" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>
                            <h4 class="text-primary">$0.00</h4>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="processSale()">Process Sale</button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#salesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter sales
function filterSales(filter) {
    console.log('Filtering by:', filter);
    // Implement filter logic here
}

// View sale details
function viewSale(id) {
    console.log('Viewing sale:', id);
    // Implement view sale logic
}

// Print receipt
function printReceipt(id) {
    window.print();
}

// Complete sale
function completeSale(id) {
    if(confirm('Are you sure you want to mark this sale as completed?')) {
        console.log('Completing sale:', id);
        // Implement complete sale logic
    }
}

// Edit sale
function editSale(id) {
    console.log('Editing sale:', id);
    // Implement edit sale logic
}

// Export sales data
function exportSalesData() {
    console.log('Exporting sales data');
    // Implement export logic
}

// New sale functions
let orderItems = [];
let totalAmount = 0;

function addProduct() {
    const productSelect = document.getElementById('productSelect');
    const quantityInput = document.getElementById('quantityInput');
    
    if(productSelect.value && quantityInput.value) {
        const productText = productSelect.options[productSelect.selectedIndex].text;
        const quantity = parseFloat(quantityInput.value);
        const price = parseFloat(productText.split('$')[1].split('/')[0]);
        
        orderItems.push({
            product: productText,
            quantity: quantity,
            price: price,
            total: price * quantity
        });
        
        updateOrderItems();
        productSelect.value = '';
        quantityInput.value = '1';
    }
}

function updateOrderItems() {
    const container = document.getElementById('orderItems');
    totalAmount = 0;
    
    if(orderItems.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No items added yet</p>';
    } else {
        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead><tbody>';
        
        orderItems.forEach((item, index) => {
            html += `<tr>
                <td>${item.product}</td>
                <td>${item.quantity}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>$${item.total.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})"><i class="fas fa-times"></i></button></td>
            </tr>`;
            totalAmount += item.total;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }
    
    updateTotal();
}

function removeItem(index) {
    orderItems.splice(index, 1);
    updateOrderItems();
}

function updateTotal() {
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const finalTotal = Math.max(0, totalAmount - discount);
    document.querySelector('.text-primary').textContent = `$${finalTotal.toFixed(2)}`;
}

document.getElementById('discountInput').addEventListener('input', updateTotal);

function processSale() {
    if(orderItems.length === 0) {
        alert('Please add at least one product');
        return;
    }
    
    console.log('Processing sale with items:', orderItems);
    // Implement sale processing logic
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('newSaleModal'));
    modal.hide();
    orderItems = [];
    updateOrderItems();
    document.getElementById('discountInput').value = '0';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/sales.blade.php ENDPATH**/ ?>