<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meat Shop POS - Complete Point of Sale System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Merged Background Layers */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        
        .bg-layer-1 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9) 0%, rgba(102, 126, 234, 0.8) 50%, rgba(118, 75, 162, 0.9) 100%);
            z-index: 3;
        }
        
        .bg-layer-2 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1553028826-f4803a7c9f6f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.3;
            z-index: 2;
            mix-blend-mode: multiply;
        }
        
        .bg-layer-3 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.4;
            z-index: 1;
            mix-blend-mode: screen;
        }
        
        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.05) 35px, rgba(255,255,255,.05) 70px),
                repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(255,255,255,.02) 35px, rgba(255,255,255,.02) 70px);
            z-index: 4;
            pointer-events: none;
        }
        
        /* Navigation Header */
        .navbar-custom {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
        }
        
        .navbar-brand-custom {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #dc3545 0%, #667eea 50%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }
        
        .navbar-brand-custom:hover {
            text-decoration: none;
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn-auth {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        .btn-login-custom {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.3);
        }
        
        .btn-login-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .btn-signup-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-signup-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        /* Main Content */
        .main-content {
            padding-top: 100px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .hero-section {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .brand-icon {
            font-size: 6rem;
            background: linear-gradient(135deg, #dc3545 0%, #667eea 50%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
            text-shadow: 0 0 30px rgba(220, 53, 69, 0.3);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #dc3545 0%, #667eea 50%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 20px rgba(102, 126, 234, 0.2);
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            font-weight: 300;
        }
        
        .hero-description {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 3rem;
            line-height: 1.8;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 4rem;
            flex-wrap: wrap;
        }
        
        .btn-cta {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary-custom:hover::before {
            left: 100%;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .btn-outline-custom {
            background: transparent;
            border: 2px solid white;
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }
        
        /* Features Section */
        .features-section {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .features-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .feature-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: white;
            margin-bottom: 1rem;
        }
        
        .feature-description {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }
        
        /* Developer Section */
        .developer-section {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .developer-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .developer-title {
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin-bottom: 2rem;
        }
        
        .developer-info {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .developer-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .developer-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .developer-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .developer-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .developer-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .copyright {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            margin-top: 2rem;
        }
        
        /* Floating animation for background elements */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }
        
        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            font-size: 3rem;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            top: 70%;
            right: 10%;
            font-size: 2.5rem;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            bottom: 10%;
            left: 20%;
            font-size: 2rem;
            animation-delay: 4s;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .developer-info {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Merged Background Layers -->
    <div class="background-container">
        <div class="bg-layer-3"></div>
        <div class="bg-layer-2"></div>
        <div class="bg-layer-1"></div>
        <div class="bg-pattern"></div>
    </div>
    
    <!-- Floating Background Elements -->
    <div class="floating-element">
        <i class="fas fa-drumstick-bite"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-shopping-basket"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-cash-register"></i>
    </div>
    
    <!-- Navigation Header -->
    <nav class="navbar-custom">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/" class="navbar-brand-custom">
                    <i class="fas fa-cut me-2"></i>
                    Meat Shop POS
                </a>
                
                <div class="auth-buttons">
                    <a href="<?php echo e(route('login')); ?>" class="btn-auth btn-login-custom">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login
                    </a>
                    <a href="/pricing" class="btn-auth btn-signup-custom">
                        <i class="fas fa-user-plus me-2"></i>
                        Sign Up
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="hero-section">
            <div class="brand-icon">
                <i class="fas fa-cut"></i>
            </div>
            
            <h1 class="hero-title">Meat Shop POS</h1>
            <p class="hero-subtitle">Complete Point of Sale System for Your Meat Business</p>
            
            <p class="hero-description">
                Transform your meat shop business with our comprehensive POS system designed specifically for meat retailers. 
                Manage inventory, process sales, track customers, and grow your business with powerful analytics 
                and reporting tools built for the modern meat shop.
            </p>
            
            <div class="cta-buttons">
                <a href="<?php echo e(route('login')); ?>" class="btn-cta btn-primary-custom">
                    <i class="fas fa-rocket me-2"></i>
                    Get Started Now
                </a>
                <a href="/pricing" class="btn-cta btn-outline-custom">
                    <i class="fas fa-crown me-2"></i>
                    View Plans
                </a>
            </div>
        </div>
        
        <!-- Features Section -->
        <div class="features-section">
            <h2 class="features-title">Powerful Features for Your Business</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="feature-title">Product Management</h3>
                    <p class="feature-description">
                        Easily manage your meat products with categories, pricing, weights, and inventory tracking. 
                        Perfect for cuts, processed meats, and specialty items.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3 class="feature-title">Inventory Tracking</h3>
                    <p class="feature-description">
                        Real-time inventory management with low-stock alerts, waste tracking, and automatic 
                        reordering suggestions to keep your shelves stocked.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="feature-title">Sales Processing</h3>
                    <p class="feature-description">
                        Fast and intuitive sales interface with barcode scanning, multiple payment methods, 
                        and automatic receipt generation.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Customer Management</h3>
                    <p class="feature-description">
                        Build customer relationships with loyalty programs, purchase history, and 
                        targeted marketing campaigns.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="feature-title">Reports & Analytics</h3>
                    <p class="feature-description">
                        Comprehensive reporting with sales trends, profit analysis, and business insights 
                        to make data-driven decisions.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Mobile Ready</h3>
                    <p class="feature-description">
                        Access your business anywhere with our mobile-responsive design and tablet 
                        compatibility for on-the-go management.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Developer Section -->
    <div class="developer-section">
        <div class="developer-content">
            <h2 class="developer-title">About the Developers</h2>
            
            <div class="developer-info">
                <div class="developer-card">
                    <div class="developer-avatar">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="developer-name">Development Team</h3>
                    <p class="developer-role">Full-Stack Developers</p>
                </div>
                
                <div class="developer-card">
                    <div class="developer-avatar">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3 class="developer-name">UI/UX Design</h3>
                    <p class="developer-role">Design & User Experience</p>
                </div>
            </div>
            
            <p class="copyright">
                <i class="fas fa-copyright me-2"></i>
                2024 Meat Shop POS System. All rights reserved. | Developed with 
                <i class="fas fa-heart text-danger mx-1"></i> 
                for the meat industry
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/welcome.blade.php ENDPATH**/ ?>