<?php $__env->startSection('title', 'Pricing Plans - Meat Shop POS'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid" style="background: linear-gradient(to bottom, #000000, #141414); color: white; min-height: 100vh;">
    
    <!-- Hero Section -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-4">Choose the right plan for your business</h1>
            <p class="lead mb-4">Switch plans or cancel anytime. No hidden fees.</p>
            <div class="d-flex justify-content-center align-items-center gap-3">
                <span class="badge bg-success p-2">
                    <i class="fas fa-check me-1"></i> Monthly Billing
                </span>
                <small class="text-muted">Save 20% with annual billing</small>
            </div>
        </div>

        <!-- Pricing Cards Netflix Style -->
        <div class="row g-4 mb-5">
            <!-- Basic Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #1a1a1a; border: 1px solid #333; color: white;">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">🟢 Basic</h3>
                        <div class="text-center mb-4">
                            <div class="d-flex align-items-end justify-content-center">
                                <span class="h1">₱</span>
                                <span class="display-3 fw-bold">1,697</span>
                                <span class="h6 mb-2">/month</span>
                            </div>
                        </div>
                        <p class="text-center text-muted mb-4">Perfect for small meat shops</p>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Up to 100 products</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Inventory tracking</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Stock alerts</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Single user access</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center text-muted">
                                <i class="fas fa-minus me-3"></i>
                                <span>No POS functionality</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center text-muted">
                                <i class="fas fa-minus me-3"></i>
                                <span>No data export</span>
                            </li>
                        </ul>
                        
                        <button class="btn btn-outline-light w-100 py-2 fw-bold" onclick="selectPlan('Basic', '₱1,697')">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>

            <!-- Standard Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 position-relative" style="background: linear-gradient(135deg, #e50914, #b20710); border: 2px solid #e50914; color: white;">
                    <div class="position-absolute top-0 start-50 translate-middle mt-3">
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold">MOST POPULAR</span>
                    </div>
                    <div class="card-body p-4 pt-5">
                        <h3 class="text-center mb-4">🔵 Standard</h3>
                        <div class="text-center mb-4">
                            <div class="d-flex align-items-end justify-content-center">
                                <span class="h1">₱</span>
                                <span class="display-3 fw-bold">4,622</span>
                                <span class="h6 mb-2">/month</span>
                            </div>
                        </div>
                        <p class="text-center mb-4">Great for growing businesses</p>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>Unlimited products</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>Full POS system</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>Customer management</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>Supplier management</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>Basic reporting</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>CSV export (limited)</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check me-3"></i>
                                <span>Up to 3 users</span>
                            </li>
                        </ul>
                        
                        <button class="btn btn-light w-100 py-2 fw-bold text-dark" onclick="selectPlan('Standard', '₱4,622')">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>

            <!-- Premium Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #1a1a1a; border: 1px solid #333; color: white;">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">🟣 Premium</h3>
                        <div class="text-center mb-4">
                            <div class="d-flex align-items-end justify-content-center">
                                <span class="h1">₱</span>
                                <span class="display-3 fw-bold">8,717</span>
                                <span class="h6 mb-2">/month</span>
                            </div>
                        </div>
                        <p class="text-center mb-4">For advanced operations</p>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>All Standard features</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Advanced analytics</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Unlimited data export</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>API access</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Batch operations</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Unlimited users</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Custom branding</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Priority support</span>
                            </li>
                        </ul>
                        
                        <button class="btn btn-outline-light w-100 py-2 fw-bold" onclick="selectPlan('Premium', '₱8,717')">
                            Get Started
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100" style="background: #1a1a1a; border: 1px solid #333; color: white;">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">🏢 Enterprise</h3>
                        <div class="text-center mb-4">
                            <div class="d-flex align-items-end justify-content-center">
                                <span class="display-3 fw-bold">Custom</span>
                            </div>
                        </div>
                        <p class="text-center mb-4">Large-scale operations</p>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Dedicated database</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Custom integrations</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>SLA guarantee</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>On-premise option</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Advanced compliance</span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span>Dedicated support</span>
                            </li>
                        </ul>
                        
                        <button class="btn btn-outline-light w-100 py-2 fw-bold" onclick="contactSales()">
                            Contact Sales
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">Everything you need to run your meat shop</h2>
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <div class="p-4">
                            <i class="fas fa-mobile-alt fa-3x mb-3 text-danger"></i>
                            <h4>Mobile POS</h4>
                            <p class="text-muted">Process sales anywhere with our mobile-friendly POS system</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-4">
                            <i class="fas fa-chart-line fa-3x mb-3 text-danger"></i>
                            <h4>Advanced Analytics</h4>
                            <p class="text-muted">Get insights into your sales performance and inventory trends</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-4">
                            <i class="fas fa-shield-alt fa-3x mb-3 text-danger"></i>
                            <h4>Secure & Reliable</h4>
                            <p class="text-muted">Bank-level security to protect your business data</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section Netflix Style -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">Frequently Asked Questions</h2>
                <div class="max-width-800 mx-auto">
                    <div class="accordion" id="faqAccordion" style="background: #1a1a1a;">
                        <div class="accordion-item" style="background: #2a2a2a; border: 1px solid #333; margin-bottom: 1px;">
                            <h2 class="accordion-header">
                                <button class="accordion-button bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is Meat Shop POS?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-dark text-white">
                                    Meat Shop POS is a complete point-of-sale and inventory management system designed specifically for meat shops, butchers, and meat retailers in the Philippines.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" style="background: #2a2a2a; border: 1px solid #333; margin-bottom: 1px;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I change my plan anytime?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-dark text-white">
                                    Yes! You can upgrade or downgrade your plan at any time. Changes take effect at the next billing cycle, and we'll prorate any differences.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" style="background: #2a2a2a; border: 1px solid #333; margin-bottom: 1px;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-dark text-white">
                                    We accept all major credit/debit cards, bank transfers, GCash, Maya, PayMaya, and other popular e-wallets in the Philippines.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" style="background: #2a2a2a; border: 1px solid #333; margin-bottom: 1px;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Is there a contract or commitment?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-dark text-white">
                                    No contracts or long-term commitments. You can cancel your subscription anytime with no cancellation fees.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" style="background: #2a2a2a; border: 1px solid #333;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Do you offer customer support?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-dark text-white">
                                    Yes! All plans include email support. Premium and Enterprise plans include priority support with faster response times and dedicated account managers.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <h3 class="mb-3">Ready to transform your meat shop?</h3>
                <p class="mb-4 text-muted">Start your free 14-day trial today. No credit card required.</p>
                <button class="btn btn-danger btn-lg px-5 py-3 fw-bold" onclick="startTrial()">
                    Start Free Trial
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.max-width-800 {
    max-width: 800px;
}

.accordion-button:not(.collapsed) {
    background-color: #2a2a2a !important;
    color: white !important;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #e50914;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(229, 9, 20, 0.3);
}

.btn-outline-light:hover {
    background-color: #e50914;
    border-color: #e50914;
}

.display-3 {
    font-size: 3.5rem;
    font-weight: 700;
}

body {
    background: #000000;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
}

::-webkit-scrollbar-thumb {
    background: #e50914;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #b20710;
}
</style>

<script>
function selectPlan(planName, price) {
    // Create a modal or redirect to checkout
    if (confirm(`You've selected the ${planName} plan for ${price}/month. Would you like to proceed?`)) {
        // In a real application, this would redirect to checkout or show a registration form
        console.log(`Selected: ${planName} - ${price}`);
        alert(`Great choice! Redirecting to checkout for ${planName} plan...`);
    }
}

function contactSales() {
    alert('Thank you for your interest in Enterprise! Our sales team will contact you within 24 hours.\n\nYou can also call us at: +63 2 1234 5678\nEmail: sales@meatshop.ph');
}

function startTrial() {
    if (confirm('Start your 14-day free trial today! No credit card required.')) {
        alert('Redirecting to trial registration...');
        // In a real application, this would redirect to trial signup
    }
}

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/pricing.blade.php ENDPATH**/ ?>