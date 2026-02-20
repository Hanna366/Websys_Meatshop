@extends('layouts.app')

@section('title', 'Profile - Meat Shop POS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">My Profile</h1>
                <button class="btn btn-primary" onclick="editProfile()">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Admin+User&size=100&background=0d6efd&color=fff" 
                             class="rounded-circle" alt="Profile" style="width: 100px; height: 100px;">
                    </div>
                    <h4 class="card-title">Admin User</h4>
                    <p class="text-muted mb-1">{{ session('email') }}</p>
                    <span class="badge bg-success">Premium Plan</span>
                </div>
            </div>

            <!-- Account Stats -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Account Statistics</h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="mb-1">247</h4>
                                <small class="text-muted">Sales</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-1">156</h4>
                            <small class="text-muted">Products</small>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="mb-1">89</h4>
                                <small class="text-muted">Customers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-1">12</h4>
                            <small class="text-muted">Suppliers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="Admin User" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="{{ session('email') }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="+63 912 345 6789" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="Administrator" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" value="Management" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" value="Manila, Philippines" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subscription Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Current Plan</label>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2">Premium</span>
                                    <span class="text-muted">$149/month</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Next Billing Date</label>
                                <p class="mb-0">March 20, 2026</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Account Status</label>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Member Since</label>
                                <p class="mb-0">January 15, 2026</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="window.location.href='/pricing'">
                            <i class="fas fa-rocket me-2"></i>Upgrade Plan
                        </button>
                        <button class="btn btn-outline-secondary" onclick="viewBilling()">
                            <i class="fas fa-file-invoice me-2"></i>View Billing History
                        </button>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Security Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Last Login</label>
                                <p class="mb-0">{{ now()->format('F j, Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Two-Factor Authentication</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="twoFactor">
                                    <label class="form-check-label" for="twoFactor">
                                        Enable 2FA
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-warning" onclick="changePassword()">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                        <button class="btn btn-outline-info" onclick="viewLoginHistory()">
                            <i class="fas fa-history me-2"></i>Login History
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editName" value="Admin User">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="editEmail" value="{{ session('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="editPhone" value="+63 912 345 6789">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" id="editLocation" value="Manila, Philippines">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="savePassword()">Change Password</button>
            </div>
        </div>
    </div>
</div>

<script>
function editProfile() {
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

function saveProfile() {
    // Simulate saving profile
    const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
    modal.hide();
    showNotification('Profile updated successfully!', 'success');
}

function changePassword() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

function savePassword() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match!', 'danger');
        return;
    }
    
    // Simulate password change
    const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
    modal.hide();
    showNotification('Password changed successfully!', 'success');
}

function viewBilling() {
    showNotification('Billing history feature coming soon!', 'info');
}

function viewLoginHistory() {
    showNotification('Login history feature coming soon!', 'info');
}

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
