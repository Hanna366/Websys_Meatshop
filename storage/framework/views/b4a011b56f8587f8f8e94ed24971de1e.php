<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Meat Shop POS - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }
        
        .sidebar .brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .brand i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #ff6b6b;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            transition: all 0.3s;
            border-radius: 0;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 0;
            min-height: 100vh;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
        
        .font-weight-bold {
            font-weight: 700 !important;
        }
        
        .text-uppercase {
            text-transform: uppercase !important;
        }
        
        .table {
            background: white;
        }
        
        .badge {
            padding: 0.5em 0.75em;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-cut"></i>
            <h4 class="mb-0">Meat Shop POS</h4>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
            <a class="nav-link" href="/products">
                <i class="fas fa-box me-2"></i>
                Products
            </a>
            <a class="nav-link" href="/inventory">
                <i class="fas fa-warehouse me-2"></i>
                Inventory
            </a>
            <a class="nav-link" href="/sales">
                <i class="fas fa-shopping-cart me-2"></i>
                Sales
            </a>
            <a class="nav-link" href="/customers">
                <i class="fas fa-users me-2"></i>
                Customers
            </a>
            <a class="nav-link" href="/suppliers">
                <i class="fas fa-truck me-2"></i>
                Suppliers
            </a>
            <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
            <a class="nav-link" href="/reports">
                <i class="fas fa-chart-bar me-2"></i>
                Reports
            </a>
            <a class="nav-link" href="/settings">
                <i class="fas fa-cog me-2"></i>
                Settings
            </a>
            <a class="nav-link bg-danger text-white" href="/pricing" style="margin-top: 10px; border-radius: 5px;">
                <i class="fas fa-crown me-2"></i>
                Upgrade Plan
            </a>
            <form action="<?php echo e(route('logout')); ?>" method="POST" style="margin: 0;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link" style="background: none; border: none; width: 100%; text-align: left; padding: 1rem 1.5rem; color: rgba(255,255,255,0.8); transition: all 0.3s; border-radius: 0;">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </button>
            </form>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            Admin User
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                            <a class="dropdown-item" href="/settings">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="<?php echo e(route('logout')); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dropdown-item" style="background: none; border: none; width: 100%; text-align: left; padding: 0.5rem 1rem;">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid p-4">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/layouts/app.blade.php ENDPATH**/ ?>