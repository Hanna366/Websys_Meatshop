<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meat Shop POS</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #dc3545 0%, #667eea 50%, #764ba2 100%);
            color: white;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
        }
        
        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }
        
        .navbar-brand:hover {
            color: white;
            text-decoration: none;
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn-login {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            border: none;
        }
        
        .btn-login:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .btn-signup {
            background: transparent;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            border: 2px solid white;
        }
        
        .btn-signup:hover {
            background: white;
            color: #667eea;
            text-decoration: none;
        }
        
        .hero {
            padding: 100px 20px 50px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            background: #dc3545;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            border: none;
            font-size: 1.1rem;
        }
        
        .btn-primary:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            border: 2px solid white;
            font-size: 1.1rem;
        }
        
        .btn-outline:hover {
            background: white;
            color: #667eea;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .features {
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .features h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 3rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .developer {
            background: rgba(0, 0, 0, 0.3);
            padding: 3rem 2rem;
            text-align: center;
            margin-top: 3rem;
        }
        
        .developer h2 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .developer-cards {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .developer-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
        }
        
        .developer-avatar {
            width: 60px;
            height: 60px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        
        .copyright {
            margin-top: 2rem;
            opacity: 0.7;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/" class="navbar-brand">
                <i class="fas fa-cut me-2"></i>
                Meat Shop POS
            </a>
            
            <div class="auth-buttons">
                <a href="<?php echo e(route('login')); ?>" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login
                </a>
                <a href="/pricing" class="btn-signup">
                    <i class="fas fa-user-plus me-2"></i>
                    Sign Up
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <div class="hero">
        <h1>Meat Shop POS</h1>
        <p>Complete Point of Sale System for Your Meat Business</p>
        
        <div class="cta-buttons">
            <a href="<?php echo e(route('login')); ?>" class="btn-primary">
                <i class="fas fa-rocket me-2"></i>
                Get Started Now
            </a>
            <a href="/pricing" class="btn-outline">
                <i class="fas fa-crown me-2"></i>
                View Plans
            </a>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="features">
        <h2>Powerful Features for Your Business</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3>Product Management</h3>
                <p>Easily manage your meat products with categories, pricing, weights, and inventory tracking.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h3>Inventory Tracking</h3>
                <p>Real-time inventory management with low-stock alerts and automatic reordering suggestions.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Sales Processing</h3>
                <p>Fast and intuitive sales interface with barcode scanning and multiple payment methods.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Customer Management</h3>
                <p>Build customer relationships with loyalty programs and purchase history tracking.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Reports & Analytics</h3>
                <p>Comprehensive reporting with sales trends and business insights for data-driven decisions.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Ready</h3>
                <p>Access your business anywhere with our mobile-responsive design and tablet compatibility.</p>
            </div>
        </div>
    </div>
    
    <!-- Developer Section -->
    <div class="developer">
        <h2>About the Developers</h2>
        
        <div class="developer-cards">
            <div class="developer-card">
                <div class="developer-avatar">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Development Team</h3>
                <p>Full-Stack Developers</p>
            </div>
            
            <div class="developer-card">
                <div class="developer-avatar">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>UI/UX Design</h3>
                <p>Design & User Experience</p>
            </div>
        </div>
        
        <div class="copyright">
            <i class="fas fa-copyright me-2"></i>
            2024 Meat Shop POS System. All rights reserved.
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views\welcome.blade.php ENDPATH**/ ?>