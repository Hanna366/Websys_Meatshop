<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meat Shop POS - Welcome</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .welcome-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .brand-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 1rem;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            color: white;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .features {
            display: flex;
            justify-content: space-around;
            margin-top: 3rem;
            flex-wrap: wrap;
        }
        
        .feature {
            text-align: center;
            margin: 1rem;
            flex: 1;
            min-width: 120px;
        }
        
        .feature i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .feature h5 {
            font-size: 0.9rem;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="brand-icon">
            <i class="fas fa-cut"></i>
        </div>
        
        <h1 class="title">Meat Shop POS</h1>
        <p class="subtitle">Complete Point of Sale System for Your Meat Business</p>
        
        <a href="/login" class="btn btn-login">
            <i class="fas fa-sign-in-alt me-2"></i>
            Enter Dashboard
        </a>
        
        <div class="features">
            <div class="feature">
                <i class="fas fa-box"></i>
                <h5>Product Management</h5>
            </div>
            <div class="feature">
                <i class="fas fa-warehouse"></i>
                <h5>Inventory Tracking</h5>
            </div>
            <div class="feature">
                <i class="fas fa-shopping-cart"></i>
                <h5>Sales Processing</h5>
            </div>
            <div class="feature">
                <i class="fas fa-users"></i>
                <h5>Customer Management</h5>
            </div>
            <div class="feature">
                <i class="fas fa-chart-bar"></i>
                <h5>Reports & Analytics</h5>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/welcome.blade.php ENDPATH**/ ?>