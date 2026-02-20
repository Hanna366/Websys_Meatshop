@extends('layouts.app')

@section('title', 'Reports - Meat Shop POS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Reports & Analytics</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                @if(session('permissions.advanced_analytics'))
                <button type="button" class="btn btn-sm btn-primary" onclick="generateAdvancedReport()">
                    <i class="fas fa-chart-line me-1"></i> Advanced Report
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportReports()">
                    <i class="fas fa-download me-1"></i> Export Reports
                </button>
                @else
                <button type="button" class="btn btn-sm btn-primary" disabled title="Advanced analytics requires Premium plan.">
                    <i class="fas fa-chart-line me-1"></i> Advanced Report
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Export requires Standard plan or higher.">
                    <i class="fas fa-download me-1"></i> Export Reports
                </button>
                @endif
            </div>
            @if(!session('permissions.advanced_analytics'))
            <div class="alert alert-warning mb-0">
                <small><i class="fas fa-exclamation-triangle me-1"></i>
                Advanced analytics and reporting require Premium plan. <a href="/pricing" class="alert-link">Upgrade now</a>.</small>
            </div>
            @endif
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select class="form-select">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-select">
                        <option value="sales">Sales Report</option>
                        <option value="inventory">Inventory Report</option>
                        <option value="customers">Customer Report</option>
                        <option value="products">Product Performance</option>
                        <option value="financial">Financial Summary</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select">
                        <option value="all">All Categories</option>
                        <option value="beef">Beef</option>
                        <option value="pork">Pork</option>
                        <option value="poultry">Poultry</option>
                        <option value="lamb">Lamb</option>
                        <option value="byproducts">Byproducts</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱345,680</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">247</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg. Sale Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱1,400</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Top Product</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Prime Rib</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Product Categories</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Detailed Sales Report</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Quantity (kg)</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Customer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2024-02-20</td>
                            <td>Prime Rib Steak</td>
                            <td>15.5</td>
                            <td>₱2,870</td>
                            <td class="text-end fw-bold">₱44,485</td>
                            <td>John Martinez</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2024-02-20</td>
                            <td>Ribeye</td>
                            <td>8.2</td>
                            <td>₱3,570</td>
                            <td class="text-end fw-bold">₱29,274</td>
                            <td>Maria Santos</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2024-02-20</td>
                            <td>Tenderloin</td>
                            <td>4.3</td>
                            <td>₱4,020</td>
                            <td class="text-end fw-bold">₱17,286</td>
                            <td>Roberto Cruz</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2024-02-20</td>
                            <td>Brisket</td>
                            <td>22.8</td>
                            <td>₱980</td>
                            <td class="text-end fw-bold">₱22,344</td>
                            <td>Linda Reyes</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2024-02-20</td>
                            <td>Chuck Roll</td>
                            <td>18.5</td>
                            <td>₱1,870</td>
                            <td class="text-end fw-bold">₱34,595</td>
                            <td>Carlos Mendoza</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2024-02-19</td>
                            <td>Short Plate</td>
                            <td>12.3</td>
                            <td>₱1,020</td>
                            <td class="text-end fw-bold">₱12,546</td>
                            <td>Antonio Dela Cruz</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2024-02-19</td>
                            <td>Oyster Blade</td>
                            <td>9.8</td>
                            <td>₱1,720</td>
                            <td class="text-end fw-bold">₱16,856</td>
                            <td>Rosa Martinez</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales (₱)',
                data: [12500, 19800, 15200, 22300, 18900, 24600, 31200],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Beef', 'Pork', 'Poultry', 'Lamb', 'Byproducts'],
            datasets: [{
                data: [45, 25, 15, 10, 5],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endsection
