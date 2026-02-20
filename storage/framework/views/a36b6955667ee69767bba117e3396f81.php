<?php $__env->startSection('title', 'Suppliers'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Supplier Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportSuppliers()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                <i class="fas fa-truck me-1"></i> Add Supplier
            </button>
        </div>
    </div>

    <!-- Supplier Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Suppliers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
                            <div class="text-xs text-gray-500">Active partnerships</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                                Active Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                            <div class="text-xs text-gray-500">Pending deliveries</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-basket fa-2x text-gray-300"></i>
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
                                Monthly Spend</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$8,456</div>
                            <div class="text-xs text-gray-500">This month</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Top Performer</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Fresh Farms</div>
                            <div class="text-xs text-gray-500">98% on-time</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Supplier Directory</h6>
            <div class="d-flex align-items-center">
                <div class="input-group me-2" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Search suppliers..." id="supplierSearch">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="supplierFilter" data-bs-toggle="dropdown">
                        <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('all')">All Suppliers</a>
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('active')">Active</a>
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('inactive')">Inactive</a>
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('preferred')">Preferred</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('beef')">Beef Suppliers</a>
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('chicken')">Chicken Suppliers</a>
                        <a class="dropdown-item" href="#" onclick="filterSuppliers('pork')">Pork Suppliers</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="suppliersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Products</th>
                            <th>Total Orders</th>
                            <th>Performance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        FF
                                    </div>
                                    <div>
                                        <div class="fw-bold">Fresh Farms</div>
                                        <small class="text-muted">Preferred</small>
                                    </div>
                                </div>
                            </td>
                            <td>John Anderson</td>
                            <td>john@freshfarms.com</td>
                            <td>+1 234-567-8901</td>
                            <td><span class="badge bg-primary">Beef</span> <span class="badge bg-info">Chicken</span></td>
                            <td>156</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 98%">98%</div>
                                </div>
                                <small>On-time delivery</small>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSupplier(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier(1)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="createOrder(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        PM
                                    </div>
                                    <div>
                                        <div class="fw-bold">Premium Meats Co.</div>
                                        <small class="text-muted">Regular</small>
                                    </div>
                                </div>
                            </td>
                            <td>Sarah Mitchell</td>
                            <td>sarah@premiummeats.com</td>
                            <td>+1 234-567-8902</td>
                            <td><span class="badge bg-warning">Pork</span> <span class="badge bg-secondary">Lamb</span></td>
                            <td>89</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 92%">92%</div>
                                </div>
                                <small>On-time delivery</small>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSupplier(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier(2)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="createOrder(2)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        QL
                                    </div>
                                    <div>
                                        <div class="fw-bold">Quality Livestock</div>
                                        <small class="text-muted">Preferred</small>
                                    </div>
                                </div>
                            </td>
                            <td>Michael Lopez</td>
                            <td>michael@qualitylivestock.com</td>
                            <td>+1 234-567-8903</td>
                            <td><span class="badge bg-primary">Beef</span> <span class="badge bg-secondary">Lamb</span></td>
                            <td>234</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 95%">95%</div>
                                </div>
                                <small>On-time delivery</small>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSupplier(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier(3)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="createOrder(3)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        SF
                                    </div>
                                    <div>
                                        <div class="fw-bold">Seafood Direct</div>
                                        <small class="text-muted">Regular</small>
                                    </div>
                                </div>
                            </td>
                            <td>Emily Foster</td>
                            <td>emily@seafooddirect.com</td>
                            <td>+1 234-567-8904</td>
                            <td><span class="badge bg-info">Fish</span></td>
                            <td>45</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 78%">78%</div>
                                </div>
                                <small>On-time delivery</small>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSupplier(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="createOrder(4)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        TG
                                    </div>
                                    <div>
                                        <div class="fw-bold">Turkey Growers Inc.</div>
                                        <small class="text-muted">Regular</small>
                                    </div>
                                </div>
                            </td>
                            <td>Robert Garcia</td>
                            <td>robert@turkeygrowers.com</td>
                            <td>+1 234-567-8905</td>
                            <td><span class="badge bg-dark">Turkey</span></td>
                            <td>67</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 88%">88%</div>
                                </div>
                                <small>On-time delivery</small>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSupplier(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier(5)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="createOrder(5)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        OM
                                    </div>
                                    <div>
                                        <div class="fw-bold">Old Meats Co.</div>
                                        <small class="text-muted">Inactive</small>
                                    </div>
                                </div>
                            </td>
                            <td>Thomas Miller</td>
                            <td>thomas@oldmeats.com</td>
                            <td>+1 234-567-8906</td>
                            <td><span class="badge bg-warning">Pork</span></td>
                            <td>12</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 65%">65%</div>
                                </div>
                                <small>On-time delivery</small>
                            </td>
                            <td><span class="badge bg-secondary">Inactive</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSupplier(6)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSupplier(6)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="reactivateSupplier(6)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav aria-label="Suppliers pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Recent Purchase Orders</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="orderFilter" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#">View All</a>
                    <a class="dropdown-item" href="#">Export Orders</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#newOrderModal">New Order</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Supplier</th>
                            <th>Products</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Expected Delivery</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#PO-2024-001</td>
                            <td>Fresh Farms</td>
                            <td>Beef, Chicken</td>
                            <td>$2,450.00</td>
                            <td>2024-02-20</td>
                            <td>2024-02-23</td>
                            <td><span class="badge bg-warning">Processing</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewOrder(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="trackOrder(1)">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#PO-2024-002</td>
                            <td>Premium Meats Co.</td>
                            <td>Pork, Lamb</td>
                            <td>$1,890.50</td>
                            <td>2024-02-19</td>
                            <td>2024-02-22</td>
                            <td><span class="badge bg-info">Shipped</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewOrder(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="trackOrder(2)">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#PO-2024-003</td>
                            <td>Quality Livestock</td>
                            <td>Beef</td>
                            <td>$3,200.00</td>
                            <td>2024-02-18</td>
                            <td>2024-02-21</td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewOrder(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="receiveOrder(3)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Supplier Modal -->
<div class="modal fade" id="newSupplierModal" tabindex="-1" aria-labelledby="newSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSupplierModalLabel">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newSupplierForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplierName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="supplierName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contactPerson" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contactPerson" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplierEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="supplierEmail" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supplierPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="supplierPhone" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productTypes" class="form-label">Product Types</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="beef" id="beefCheck">
                                <label class="form-check-label" for="beefCheck">Beef</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="chicken" id="chickenCheck">
                                <label class="form-check-label" for="chickenCheck">Chicken</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="pork" id="porkCheck">
                                <label class="form-check-label" for="porkCheck">Pork</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="lamb" id="lambCheck">
                                <label class="form-check-label" for="lambCheck">Lamb</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="turkey" id="turkeyCheck">
                                <label class="form-check-label" for="turkeyCheck">Turkey</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="fish" id="fishCheck">
                                <label class="form-check-label" for="fishCheck">Fish</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supplierType" class="form-label">Supplier Type</label>
                            <select class="form-select" id="supplierType" required>
                                <option value="regular">Regular</option>
                                <option value="preferred">Preferred</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplierAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="supplierAddress" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="paymentTerms" class="form-label">Payment Terms</label>
                            <select class="form-select" id="paymentTerms">
                                <option value="net30">Net 30</option>
                                <option value="net15">Net 15</option>
                                <option value="cod">Cash on Delivery</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deliveryLeadTime" class="form-label">Delivery Lead Time (days)</label>
                            <input type="number" class="form-control" id="deliveryLeadTime" min="1" value="3">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSupplier()">Save Supplier</button>
            </div>
        </div>
    </div>
</div>

<!-- New Order Modal -->
<div class="modal fade" id="newOrderModal" tabindex="-1" aria-labelledby="newOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newOrderModalLabel">Create Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newOrderForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="orderSupplier" class="form-label">Supplier</label>
                            <select class="form-select" id="orderSupplier" required>
                                <option value="">Select Supplier</option>
                                <option value="1">Fresh Farms</option>
                                <option value="2">Premium Meats Co.</option>
                                <option value="3">Quality Livestock</option>
                                <option value="4">Seafood Direct</option>
                                <option value="5">Turkey Growers Inc.</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expectedDelivery" class="form-label">Expected Delivery Date</label>
                            <input type="date" class="form-control" id="expectedDelivery" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Order Items</label>
                        <div id="orderItems" class="border rounded p-3 bg-light">
                            <p class="text-muted mb-0">No items added yet</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productSelect" class="form-label">Add Product</label>
                            <select class="form-select" id="productSelect">
                                <option value="">Select Product</option>
                                <option value="beef">Beef - $12.50/kg</option>
                                <option value="chicken">Chicken - $8.75/kg</option>
                                <option value="pork">Pork - $10.25/kg</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity (kg)</label>
                            <input type="number" class="form-control" id="quantity" min="1" step="0.1">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">&nbsp;</label><br>
                            <button type="button" class="btn btn-primary w-100" onclick="addOrderItem()">Add</button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="orderNotes" class="form-label">Order Notes</label>
                        <textarea class="form-control" id="orderNotes" rows="3"></textarea>
                    </div>
                    
                    <div class="text-end">
                        <h5>Total: $<span id="orderTotal">0.00</span></h5>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createPurchaseOrder()">Create Order</button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('supplierSearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#suppliersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter suppliers
function filterSuppliers(filter) {
    console.log('Filtering suppliers by:', filter);
    // Implement filter logic here
}

// Supplier actions
function viewSupplier(id) {
    console.log('Viewing supplier:', id);
    // Implement view functionality
}

function editSupplier(id) {
    console.log('Editing supplier:', id);
    // Implement edit functionality
}

function createOrder(id) {
    console.log('Creating order for supplier:', id);
    // Pre-select supplier in order modal
    document.getElementById('orderSupplier').value = id;
    const modal = new bootstrap.Modal(document.getElementById('newOrderModal'));
    modal.show();
}

function reactivateSupplier(id) {
    if(confirm('Are you sure you want to reactivate this supplier?')) {
        console.log('Reactivating supplier:', id);
        // Implement reactivate functionality
    }
}

// Order actions
function viewOrder(id) {
    console.log('Viewing order:', id);
    // Implement view order functionality
}

function trackOrder(id) {
    console.log('Tracking order:', id);
    // Implement track order functionality
}

function receiveOrder(id) {
    console.log('Receiving order:', id);
    // Implement receive order functionality
}

// New supplier functions
function saveSupplier() {
    const form = document.getElementById('newSupplierForm');
    if(form.checkValidity()) {
        console.log('Saving new supplier');
        // Implement save functionality
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('newSupplierModal'));
        modal.hide();
        form.reset();
    } else {
        form.reportValidity();
    }
}

// Order management
let orderItems = [];
let orderTotal = 0;

function addOrderItem() {
    const productSelect = document.getElementById('productSelect');
    const quantityInput = document.getElementById('quantity');
    
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
        quantityInput.value = '';
    }
}

function updateOrderItems() {
    const container = document.getElementById('orderItems');
    orderTotal = 0;
    
    if(orderItems.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No items added yet</p>';
    } else {
        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead><tbody>';
        
        orderItems.forEach((item, index) => {
            html += `<tr>
                <td>${item.product}</td>
                <td>${item.quantity} kg</td>
                <td>$${item.price.toFixed(2)}/kg</td>
                <td>$${item.total.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOrderItem(${index})"><i class="fas fa-times"></i></button></td>
            </tr>`;
            orderTotal += item.total;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }
    
    document.getElementById('orderTotal').textContent = orderTotal.toFixed(2);
}

function removeOrderItem(index) {
    orderItems.splice(index, 1);
    updateOrderItems();
}

function createPurchaseOrder() {
    if(orderItems.length === 0) {
        alert('Please add at least one item to the order');
        return;
    }
    
    const form = document.getElementById('newOrderForm');
    if(form.checkValidity()) {
        console.log('Creating purchase order with items:', orderItems);
        // Implement create order functionality
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('newOrderModal'));
        modal.hide();
        form.reset();
        orderItems = [];
        updateOrderItems();
    } else {
        form.reportValidity();
    }
}

function exportSuppliers() {
    console.log('Exporting supplier data');
    // Implement export functionality
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/suppliers.blade.php ENDPATH**/ ?>