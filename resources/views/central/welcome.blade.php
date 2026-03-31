<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meat Shop POS - Central Management Platform</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #dc3545 100%);
            color: white;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
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
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .btn-signup {
            background: transparent;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            border: 2px solid white;
            transition: all 0.3s ease;
        }
        
        .btn-signup:hover {
            background: white;
            color: #667eea;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .hero {
            padding: 120px 20px 80px;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero .subtitle {
            font-size: 1.4rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            font-weight: 300;
        }
        
        .hero .description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 4rem;
        }
        
        .btn-primary {
            background: #dc3545;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            border: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-primary:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            border: 2px solid white;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: white;
            color: #667eea;
            text-decoration: none;
            transform: translateY(-3px);
        }
        
        .services {
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .services h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        
        .services .subtitle {
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.9;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .service-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .service-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, #dc3545, #667eea);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .service-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .service-description {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.5;
        }
        
        .stats {
            background: rgba(0, 0, 0, 0.2);
            padding: 4rem 2rem;
            text-align: center;
            margin-top: 3rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .footer {
            background: rgba(0, 0, 0, 0.3);
            padding: 2rem;
            text-align: center;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero .subtitle {
                font-size: 1.2rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .auth-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="d-flex justify-content-between align-items-center w-100">
            <a href="/central" class="navbar-brand">
                <i class="fas fa-cut me-2"></i>
                Meat Shop POS Central
            </a>
            
            <div class="auth-buttons">
                <a href="{{ route('login') }}" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login
                </a>
                <a href="{{ route('tenants.create') }}" class="btn-signup">
                    <i class="fas fa-user-plus me-2"></i>
                    Sign Up
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <div class="hero">
        <h1>Central Management Platform</h1>
        <p class="subtitle">Complete SaaS Solution for Meat Shop Businesses</p>
        <p class="description">
            Welcome to the Meat Shop POS Central Management Platform. Our comprehensive SaaS solution empowers meat shop owners with powerful inventory management, point-of-sale systems, and business analytics - all in one unified platform designed specifically for the meat industry.
        </p>
        
        <div class="cta-buttons">
            <a href="{{ route('tenants.create') }}" class="btn-primary">
                <i class="fas fa-rocket me-2"></i>
                Start Your Business
            </a>
            <a href="{{ route('login') }}" class="btn-outline">
                <i class="fas fa-sign-in-alt me-2"></i>
                Access Your Account
            </a>
        </div>
    </div>
    
    <!-- Services Section -->
    <div class="services">
        <h2>What We Offer</h2>
        <p class="subtitle">Comprehensive services designed for meat shop success</p>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h3>Multi-Tenant Management</h3>
                <p class="service-description">
                    Manage multiple meat shop locations from a single central dashboard. Each tenant gets isolated data while you maintain complete oversight of all operations.
                </p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3>Advanced POS System</h3>
                <p class="service-description">
                    Weight-based sales processing perfect for meat products. Support for multiple payment methods, barcode scanning, and real-time inventory updates.
                </p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h3>Inventory Management</h3>
                <p class="service-description">
                    Batch-level tracking with expiry monitoring, automatic reordering suggestions, and real-time stock alerts to prevent waste and ensure freshness.
                </p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Business Analytics</h3>
                <p class="service-description">
                    Comprehensive reporting with sales trends, profit analysis, customer behavior insights, and performance metrics to drive data-driven decisions.
                </p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Customer Management</h3>
                <p class="service-description">
                    Build lasting customer relationships with loyalty programs, purchase history tracking, and targeted marketing campaigns for repeat business.
                </p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3>Offline Operations</h3>
                <p class="service-description">
                    Continue sales operations even without internet connectivity. Automatic data synchronization when connectivity is restored ensures no data loss.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Stats Section -->
    <div class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Uptime Guarantee</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support Available</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4.9★</div>
                <div class="stat-label">Customer Rating</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Active Shops</div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p class="mb-2">
            <i class="fas fa-copyright me-2"></i>
            2024 Meat Shop POS Central. All rights reserved.
        </p>
        <p class="mb-0 opacity-75">
            Built with ❤️ for meat shop owners worldwide
        </p>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
