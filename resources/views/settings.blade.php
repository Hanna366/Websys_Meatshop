@extends('layouts.app')

@section('title', 'Settings - Meat Shop POS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">System Settings</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Settings Navigation -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Settings Menu</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#general" class="list-group-item list-group-item-action active">
                        <i class="fas fa-cog me-2"></i> General Settings
                    </a>
                    <a href="#business" class="list-group-item list-group-item-action">
                        <i class="fas fa-store me-2"></i> Business Info
                    </a>
                    <a href="#tax" class="list-group-item list-group-item-action">
                        <i class="fas fa-receipt me-2"></i> Tax & Currency
                    </a>
                    <a href="#inventory" class="list-group-item list-group-item-action">
                        <i class="fas fa-boxes me-2"></i> Inventory
                    </a>
                    <a href="#users" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> User Management
                    </a>
                    <a href="#backup" class="list-group-item list-group-item-action">
                        <i class="fas fa-database me-2"></i> Backup & Restore
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <!-- General Settings -->
            <div class="card mb-4" id="general">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shop Name</label>
                                <input type="text" class="form-control" value="Meat Shop POS" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shop Email</label>
                                <input type="email" class="form-control" value="admin@meatshop.com" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="+63 912 3456" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" value="123 Market Street, Manila" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Time Zone</label>
                                <select class="form-select">
                                    <option value="UTC+8" selected>Asia/Manila (UTC+8)</option>
                                    <option value="UTC+9">Asia/Tokyo (UTC+9)</option>
                                    <option value="UTC+7">Asia/Bangkok (UTC+7)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date Format</label>
                                <select class="form-select">
                                    <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                    <option value="d/m/Y">DD/MM/YYYY</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Info -->
            <div class="card mb-4" id="business">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Business Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Business Name</label>
                                <input type="text" class="form-control" value="Premium Meat Shop Inc." />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Business Type</label>
                                <select class="form-select">
                                    <option value="retail" selected>Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tax ID / VAT Number</label>
                                <input type="text" class="form-control" value="123-456-789-000" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Business License</label>
                                <input type="text" class="form-control" value="BL-2024-12345" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Business Description</label>
                                <textarea class="form-control" rows="3">Premium quality meat products serving the community since 2010. We offer the finest cuts of beef, pork, poultry, and lamb with competitive prices and excellent customer service.</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax & Currency -->
            <div class="card mb-4" id="tax">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Tax & Currency Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-select">
                                    <option value="PHP" selected>Philippine Peso (₱)</option>
                                    <option value="USD">US Dollar ($)</option>
                                    <option value="EUR">Euro (€)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency Symbol Position</label>
                                <select class="form-select">
                                    <option value="before" selected>Before (₱100.00)</option>
                                    <option value="after">After (100.00₱)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">VAT Rate (%)</label>
                                <input type="number" class="form-control" value="12" step="0.1" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Service Charge (%)</label>
                                <input type="number" class="form-control" value="0" step="0.1" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="taxIncluded" checked>
                                <label class="form-check-label" for="taxIncluded">
                                    Include tax in product prices
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Settings -->
            <div class="card mb-4" id="inventory">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Inventory Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Low Stock Alert (%)</label>
                                <input type="number" class="form-control" value="20" min="1" max="100" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Out of Stock Alert</label>
                                <select class="form-select">
                                    <option value="email" selected>Email Notification</option>
                                    <option value="sms">SMS Notification</option>
                                    <option value="both">Both</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="autoReorder" checked>
                                <label class="form-check-label" for="autoReorder">
                                    Enable automatic reorder suggestions
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="trackExpiry" checked>
                                <label class="form-check-label" for="trackExpiry">
                                    Track product expiry dates
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="batchTracking">
                                <label class="form-check-label" for="batchTracking">
                                    Enable batch tracking
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="card mb-4" id="users">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">User Management</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>admin</td>
                                    <td>admin@meatshop.com</td>
                                    <td><span class="badge bg-danger">Administrator</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>cashier1</td>
                                    <td>cashier1@meatshop.com</td>
                                    <td><span class="badge bg-primary">Cashier</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>manager</td>
                                    <td>manager@meatshop.com</td>
                                    <td><span class="badge bg-warning">Manager</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-primary mt-3">
                        <i class="fas fa-user-plus me-1"></i> Add New User
                    </button>
                </div>
            </div>

            <!-- Backup & Restore -->
            <div class="card mb-4" id="backup">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Backup & Restore</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Manual Backup</h6>
                            <p class="text-muted">Create a backup of your data</p>
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download Backup
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Restore Backup</h6>
                            <p class="text-muted">Restore from a backup file</p>
                            <input type="file" class="form-control mb-2" accept=".sql,.json">
                            <button type="button" class="btn btn-warning">
                                <i class="fas fa-upload me-1"></i> Restore Backup
                            </button>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Automatic Backup Settings</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="autoBackup" checked>
                                <label class="form-check-label" for="autoBackup">
                                    Enable automatic backups
                                </label>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Backup Frequency</label>
                                    <select class="form-select">
                                        <option value="daily" selected>Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Retention Period</label>
                                    <select class="form-select">
                                        <option value="7" selected>7 days</option>
                                        <option value="30">30 days</option>
                                        <option value="90">90 days</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
