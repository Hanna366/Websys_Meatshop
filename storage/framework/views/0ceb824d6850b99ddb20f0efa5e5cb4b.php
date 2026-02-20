<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Meat Shop POS</title>
    
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
        
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .brand i {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .brand h1 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #343a40;
            margin: 0;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 20px;
            font-size: 1rem;
            border-radius: 10px;
            width: 100%;
            color: white;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .demo-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand">
            <i class="fas fa-cut"></i>
            <h1>Meat Shop POS</h1>
        </div>
        
        <?php if(session('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo e(route('login.post')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label for="email" class="form-label fw-bold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-envelope text-muted"></i>
                    </span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                           placeholder="Enter your email" value="<?php echo e(old('email')); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
            </button>
        </form>
        
        <div class="alert alert-info demo-info mt-4">
            <h6><i class="fas fa-info-circle me-2"></i><strong>Demo Accounts by Subscription Plan:</strong></h6>
            
            <div class="row mt-3">
                <!-- Basic Plan -->
                <div class="col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white text-center py-2">
                            <i class="fas fa-star me-2"></i><strong>Basic Plan</strong>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-2">
                                <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                            </div>
                            <p><strong>Email:</strong> <code>basic@meatshop.com</code></p>
                            <p><strong>Password:</strong> <code>basic123</code></p>
                            <small class="text-muted">Up to 100 products, Inventory tracking, Single user</small>
                        </div>
                    </div>
                </div>
                
                <!-- Standard Plan -->
                <div class="col-md-4 mb-3">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark text-center py-2">
                            <i class="fas fa-crown me-2"></i><strong>Standard Plan</strong>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-2">
                                <i class="fas fa-user-circle fa-2x text-warning mb-2"></i>
                            </div>
                            <p><strong>Email:</strong> <code>standard@meatshop.com</code></p>
                            <p><strong>Password:</strong> <code>standard123</code></p>
                            <small class="text-muted">Unlimited products, Full POS, Up to 3 users, Customer management</small>
                        </div>
                    </div>
                </div>
                
                <!-- Premium Plan -->
                <div class="col-md-4 mb-3">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white text-center py-2">
                            <i class="fas fa-gem me-2"></i><strong>Premium Plan</strong>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-2">
                                <i class="fas fa-user-circle fa-2x text-danger mb-2"></i>
                            </div>
                            <p><strong>Email:</strong> <code>premium@meatshop.com</code></p>
                            <p><strong>Password:</strong> <code>premium123</code></p>
                            <small class="text-muted">All features, Advanced analytics, API access, Unlimited users, Priority support</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH C:\Users\Rusty\Music\MeatShop\resources\views/auth/login.blade.php ENDPATH**/ ?>