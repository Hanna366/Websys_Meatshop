

<?php $__env->startSection('title', 'Pricing Plans - Meat Shop POS'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid" style="background: linear-gradient(to bottom, #000000, #141414); color: white; min-height: 100vh;">
    
    <!-- Hero Section -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Go plan</h1>
            <p class="lead">Choose the perfect plan for your meat shop business</p>
        </div>

        <!-- Pricing Cards -->
        <div class="row g-4 mb-5">
            <!-- Basic Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #2d2d2d; border: 1px solid #404040;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="badge bg-success mb-2">🟢 Basic Plan</div>
                            <h3 class="card-title text-white">Basic</h3>
                            <p class="text-muted">Designed for small shops with simple needs</p>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h2 class="text-white">$29</h2>
                            <p class="text-muted">Monthly subscription</p>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Up to 100 products</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Inventory tracking and stock alerts</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Single user access</li>
                            <li class="mb-2 text-muted"><i class="fas fa-times text-danger me-2"></i>No POS functionality</li>
                            <li class="mb-2 text-muted"><i class="fas fa-times text-danger me-2"></i>No data export</li>
                        </ul>

                        <button class="btn btn-outline-light w-100" onclick="selectPlan('Basic', 29)">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>

            <!-- Standard Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #2d2d2d; border: 1px solid #404040;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="badge bg-primary mb-2">🔵 Standard Plan</div>
                            <h3 class="card-title text-white">Standard</h3>
                            <p class="text-muted">Suitable for growing businesses</p>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h2 class="text-white">$79</h2>
                            <p class="text-muted">Monthly subscription</p>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited products</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Full POS system</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Supplier and customer management</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Basic reporting</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>CSV export (limited)</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Up to 3 users</li>
                        </ul>

                        <button class="btn btn-outline-light w-100" onclick="selectPlan('Standard', 79)">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>

            <!-- Premium Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #2d2d2d; border: 2px solid #ff6b6b;">
                    <div class="card-body p-4 position-relative">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-danger">POPULAR</span>
                        </div>
                        
                        <div class="text-center mb-4">
                            <div class="badge bg-danger mb-2">🟣 Premium Plan</div>
                            <h3 class="card-title text-white">Premium</h3>
                            <p class="text-muted">For advanced operations</p>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h2 class="text-white">$149</h2>
                            <p class="text-muted">Monthly subscription</p>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>All Standard features</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Advanced analytics dashboard</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited data export (CSV, Excel, PDF)</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>API access</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Batch operations</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited users</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Custom branding</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>SMS notifications and priority support</li>
                        </ul>

                        <button class="btn btn-danger w-100" onclick="selectPlan('Premium', 149)">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #1a1a1a; border: 2px solid #gold;">
                    <div class="card-body p-4 position-relative">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-warning text-dark">ENTERPRISE</span>
                        </div>
                        
                        <div class="text-center mb-4">
                            <div class="badge bg-warning text-dark mb-2">🏢 Enterprise Plan</div>
                            <h3 class="card-title text-white">Enterprise</h3>
                            <p class="text-muted">Custom Pricing</p>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h2 class="text-white">Custom</h2>
                            <p class="text-muted">Contact for pricing</p>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Dedicated database</li>
                            <li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Custom integrations</li>
                            <li class="mb-2"><i class="fas fa-check text-warning me-2"></i>SLA and priority services</li>
                            <li class="mb-2"><i class="fas fa-check text-warning me-2"></i>On-premise deployment option</li>
                            <li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Advanced compliance tools</li>
                        </ul>

                        <button class="btn btn-warning w-100 text-dark" onclick="contactSales()">
                            Contact Sales
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background: #2d2d2d; color: white;">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="paymentModalLabel">Complete Your Subscription</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4 id="selectedPlanName" class="text-white">Premium Plan</h4>
                        <p class="text-muted">Top features • Smarter, faster responses with GPT-5 • More messages & uploads • Create more images, faster • Extra memory & context</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <h5 class="text-white">Monthly subscription</h5>
                            <h3 class="text-white" id="planPrice">₱267.86</h3>
                        </div>
                        <div class="col-6">
                            <h5 class="text-white">Promotion</h5>
                            <p class="text-success"><strong>-₱267.86</strong></p>
                            <p class="text-success">100% off for a month</p>
                        </div>
                    </div>

                    <div class="border-top border-secondary pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>VAT (0%)</span>
                            <span>₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Due today</strong>
                            <strong class="text-success" id="totalDue">₱0.00</strong>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-3">
                        <label class="form-label text-white">Select Payment Method</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="paymentMethod" id="creditCard" autocomplete="off" checked>
                            <label class="btn btn-outline-light" for="creditCard">
                                <i class="fas fa-credit-card me-2"></i>Credit Card
                            </label>

                            <input type="radio" class="btn-check" name="paymentMethod" id="gcash" autocomplete="off">
                            <label class="btn btn-outline-light" for="gcash">
                                <i class="fas fa-mobile-alt me-2"></i>GCash
                            </label>

                            <input type="radio" class="btn-check" name="paymentMethod" id="paypal" autocomplete="off">
                            <label class="btn btn-outline-light" for="paypal">
                                <i class="fab fa-paypal me-2"></i>PayPal
                            </label>
                        </div>
                    </div>

                    <!-- Credit Card Form (shown by default) -->
                    <div id="creditCardForm">
                        <div class="mb-3">
                            <label class="form-label text-white">Card Number</label>
                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">CVV</label>
                                <input type="text" class="form-control" placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Cardholder Name</label>
                            <input type="text" class="form-control" placeholder="John Doe">
                        </div>
                    </div>

                    <!-- GCash Form (hidden by default) -->
                    <div id="gcashForm" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label text-white">GCash Number</label>
                            <input type="tel" class="form-control" placeholder="09XX XXX XXXX">
                        </div>
                        <div class="text-center">
                            <img src="https://via.placeholder.com/150x50/004E98/FFFFFF?text=GCash" alt="GCash" class="img-fluid">
                        </div>
                    </div>

                    <!-- PayPal Form (hidden by default) -->
                    <div id="paypalForm" style="display: none;">
                        <div class="text-center">
                            <p class="text-white">You will be redirected to PayPal to complete your payment.</p>
                            <img src="https://via.placeholder.com/200x50/003087/FFFFFF?text=PayPal" alt="PayPal" class="img-fluid">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="processPayment()">
                        <i class="fas fa-lock me-2"></i>Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="background: #2d2d2d; color: white;">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="successModalLabel">Payment Successful!</h5>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h4 class="text-white mb-3">Welcome to Premium!</h4>
                    <p class="text-muted">Your subscription has been activated successfully.</p>
                    <p class="text-white">You now have access to all premium features.</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-success" onclick="goToDashboard()">
                        Go to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedPlan = '';
let selectedPrice = 0;

function selectPlan(plan, price) {
    selectedPlan = plan;
    selectedPrice = price;
    
    // Update modal content
    document.getElementById('selectedPlanName').textContent = plan + ' Plan';
    document.getElementById('planPrice').textContent = '$' + price.toFixed(2);
    
    // Show payment modal
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

function contactSales() {
    // Create contact modal
    const contactModal = `
        <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="background: #2d2d2d; color: white;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title" id="contactModalLabel">Contact Enterprise Sales</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-building fa-3x text-warning mb-3"></i>
                            <h4 class="text-white">Enterprise Solutions</h4>
                            <p class="text-muted">Get custom pricing and solutions tailored to your business needs</p>
                        </div>
                        
                        <form id="contactForm">
                            <div class="mb-3">
                                <label class="form-label text-white">Company Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Your Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Business Email</label>
                                <input type="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Phone Number</label>
                                <input type="tel" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Company Size</label>
                                <select class="form-select">
                                    <option value="">Select company size</option>
                                    <option value="small">1-50 employees</option>
                                    <option value="medium">51-200 employees</option>
                                    <option value="large">201-1000 employees</option>
                                    <option value="enterprise">1000+ employees</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Message</label>
                                <textarea class="form-control" rows="3" placeholder="Tell us about your needs..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning text-dark" onclick="submitContact()">
                            <i class="fas fa-paper-plane me-2"></i>Send Inquiry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing contact modal if any
    const existingModal = document.getElementById('contactModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', contactModal);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    modal.show();
}

function submitContact() {
    // Simulate form submission
    const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
    modal.hide();
    
    // Show success message
    showNotification('Thank you! Our enterprise sales team will contact you within 24 hours.', 'success');
}

// Payment method switching
document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all payment forms
        document.getElementById('creditCardForm').style.display = 'none';
        document.getElementById('gcashForm').style.display = 'none';
        document.getElementById('paypalForm').style.display = 'none';
        
        // Show selected payment form
        if (this.id === 'creditCard') {
            document.getElementById('creditCardForm').style.display = 'block';
        } else if (this.id === 'gcash') {
            document.getElementById('gcashForm').style.display = 'block';
        } else if (this.id === 'paypal') {
            document.getElementById('paypalForm').style.display = 'block';
        }
    });
});

function processPayment() {
    // Simulate payment processing
    const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
    modal.hide();
    
    // Show success modal
    setTimeout(() => {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    }, 500);
}

function goToDashboard() {
    // Update session to premium
    fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            email: 'premium@meatshop.com',
            password: 'premium123'
        })
    }).then(() => {
        window.location.href = '/dashboard';
    });
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/pricing.blade.php ENDPATH**/ ?>