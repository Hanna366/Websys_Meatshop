@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Customer Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportCustomers()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                <i class="fas fa-user-plus me-1"></i> Add Customer
            </button>
        </div>
    </div>

    <!-- Customer Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,234</div>
                            <div class="text-xs text-gray-500">+12% this month</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Active Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">892</div>
                            <div class="text-xs text-gray-500">Last 30 days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                New This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">67</div>
                            <div class="text-xs text-gray-500">February 2024</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                                VIP Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">45</div>
                            <div class="text-xs text-gray-500">Premium members</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Customer Directory</h6>
            <div class="d-flex align-items-center">
                <div class="input-group me-2" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Search customers..." id="customerSearch">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="customerFilter" data-bs-toggle="dropdown">
                        <i class="fas fa-filter fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="#" onclick="filterCustomers('all')">All Customers</a>
                        <a class="dropdown-item" href="#" onclick="filterCustomers('active')">Active</a>
                        <a class="dropdown-item" href="#" onclick="filterCustomers('inactive')">Inactive</a>
                        <a class="dropdown-item" href="#" onclick="filterCustomers('vip')">VIP</a>
                        <a class="dropdown-item" href="#" onclick="filterCustomers('new')">New</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="filterCustomers('month')">This Month</a>
                        <a class="dropdown-item" href="#" onclick="filterCustomers('year')">This Year</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="customersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Orders</th>
                            <th>Total Spent</th>
                            <th>Member Since</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#CUST-001</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        JS
                                    </div>
                                    <div>
                                        <div class="fw-bold">John Smith</div>
                                        <small class="text-muted">VIP Member</small>
                                    </div>
                                </div>
                            </td>
                            <td>john.smith@email.com</td>
                            <td>+1 234-567-8901</td>
                            <td>45</td>
                            <td>$2,345.67</td>
                            <td>2023-01-15</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomer(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCustomer(1)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewOrders(1)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#CUST-002</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        SJ
                                    </div>
                                    <div>
                                        <div class="fw-bold">Sarah Johnson</div>
                                        <small class="text-muted">Regular</small>
                                    </div>
                                </div>
                            </td>
                            <td>sarah.j@email.com</td>
                            <td>+1 234-567-8902</td>
                            <td>23</td>
                            <td>$890.45</td>
                            <td>2023-03-22</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomer(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCustomer(2)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewOrders(2)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#CUST-003</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        MW
                                    </div>
                                    <div>
                                        <div class="fw-bold">Mike Wilson</div>
                                        <small class="text-muted">Regular</small>
                                    </div>
                                </div>
                            </td>
                            <td>mike.wilson@email.com</td>
                            <td>+1 234-567-8903</td>
                            <td>67</td>
                            <td>$3,456.78</td>
                            <td>2022-11-08</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomer(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCustomer(3)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewOrders(3)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#CUST-004</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        ED
                                    </div>
                                    <div>
                                        <div class="fw-bold">Emily Davis</div>
                                        <small class="text-muted">New</small>
                                    </div>
                                </div>
                            </td>
                            <td>emily.davis@email.com</td>
                            <td>+1 234-567-8904</td>
                            <td>3</td>
                            <td>$156.89</td>
                            <td>2024-02-10</td>
                            <td><span class="badge bg-info">New</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomer(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCustomer(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewOrders(4)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#CUST-005</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        RB
                                    </div>
                                    <div>
                                        <div class="fw-bold">Robert Brown</div>
                                        <small class="text-muted">VIP Member</small>
                                    </div>
                                </div>
                            </td>
                            <td>robert.brown@email.com</td>
                            <td>+1 234-567-8905</td>
                            <td>89</td>
                            <td>$5,678.90</td>
                            <td>2022-05-12</td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomer(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCustomer(5)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewOrders(5)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#CUST-006</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                        LT
                                    </div>
                                    <div>
                                        <div class="fw-bold">Lisa Thompson</div>
                                        <small class="text-muted">Inactive</small>
                                    </div>
                                </div>
                            </td>
                            <td>lisa.t@email.com</td>
                            <td>+1 234-567-8906</td>
                            <td>12</td>
                            <td>$445.23</td>
                            <td>2023-08-15</td>
                            <td><span class="badge bg-secondary">Inactive</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCustomer(6)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCustomer(6)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewOrders(6)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav aria-label="Customers pagination">
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

    <!-- Customer Analytics -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Growth</h6>
                </div>
                <div class="card-body">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Types</h6>
                </div>
                <div class="card-body">
                    <canvas id="customerTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCustomerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newCustomerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customerType" class="form-label">Customer Type</label>
                            <select class="form-select" id="customerType" required>
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-control" id="birthdate">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="zipCode" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" id="zipCode">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="emailConsent">
                            <label class="form-check-label" for="emailConsent">
                                Email marketing consent
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomer()">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerDetailsModalLabel">Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="customerDetailsContent">
                    <!-- Customer details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editCustomerFromDetails()">Edit Customer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Search functionality
document.getElementById('customerSearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#customersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter customers
function filterCustomers(filter) {
    console.log('Filtering customers by:', filter);
    // Implement filter logic here
}

// Customer actions
function viewCustomer(id) {
    console.log('Viewing customer:', id);
    // Load customer details into modal
    const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
    document.getElementById('customerDetailsContent').innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 36px;">
                    JS
                </div>
                <h5>John Smith</h5>
                <span class="badge bg-success">Active</span>
                <span class="badge bg-warning">VIP Member</span>
            </div>
            <div class="col-md-8">
                <h6>Contact Information</h6>
                <p><strong>Email:</strong> john.smith@email.com</p>
                <p><strong>Phone:</strong> +1 234-567-8901</p>
                <p><strong>Address:</strong> 123 Main St, City, State 12345</p>
                
                <h6 class="mt-3">Purchase History</h6>
                <p><strong>Total Orders:</strong> 45</p>
                <p><strong>Total Spent:</strong> $2,345.67</p>
                <p><strong>Member Since:</strong> January 15, 2023</p>
                
                <h6 class="mt-3">Recent Orders</h6>
                <ul>
                    <li>#ORD-001 - $156.78 (2024-02-20)</li>
                    <li>#ORD-003 - $234.12 (2024-02-19)</li>
                    <li>#ORD-007 - $89.45 (2024-02-18)</li>
                </ul>
            </div>
        </div>
    `;
    modal.show();
}

function editCustomer(id) {
    console.log('Editing customer:', id);
    // Implement edit functionality
}

function viewOrders(id) {
    console.log('Viewing orders for customer:', id);
    // Implement view orders functionality
}

function editCustomerFromDetails() {
    console.log('Editing customer from details view');
    // Implement edit functionality
}

// New customer functions
function saveCustomer() {
    const form = document.getElementById('newCustomerForm');
    if(form.checkValidity()) {
        console.log('Saving new customer');
        // Implement save functionality
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('newCustomerModal'));
        modal.hide();
        form.reset();
    } else {
        form.reportValidity();
    }
}

function exportCustomers() {
    console.log('Exporting customer data');
    // Implement export functionality
}

// Customer Growth Chart
const growthCtx = document.getElementById('customerGrowthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'New Customers',
            data: [45, 52, 48, 65, 59, 72, 68, 81, 75, 89, 92, 67],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        }
    }
});

// Customer Type Chart
const typeCtx = document.getElementById('customerTypeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: ['Regular', 'VIP', 'New', 'Inactive'],
        datasets: [{
            data: [847, 45, 67, 275],
            backgroundColor: [
                '#36A2EB',
                '#FF6384',
                '#FFCE56',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection
