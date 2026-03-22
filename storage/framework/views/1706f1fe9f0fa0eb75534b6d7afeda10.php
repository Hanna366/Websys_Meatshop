<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Meat Shop Branch - <?php echo $__env->yieldContent('title', 'Tenant Dashboard'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fc;
        }

        .sidebar {
            height: 100vh;
            background: linear-gradient(155deg, #1a1b4b 0%, #2f467a 55%, #ce4a3e 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s;
        }

        .sidebar .brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.84);
            padding: 0.95rem 1.4rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .navbar {
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 1rem 1.8rem;
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
    <div class="sidebar">
        <div class="brand">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-cut"></i>
                <h5 class="mb-0">Branch POS</h5>
            </div>
            <?php if(tenant()): ?>
                <small class="text-white-50"><?php echo e(tenant()->business_name ?? tenant()->tenant_id); ?></small>
            <?php elseif(session('user.name')): ?>
                <small class="text-white-50"><?php echo e(session('user.name')); ?></small>
            <?php endif; ?>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?php echo e(request()->routeIs('tenant.dashboard') ? 'active' : ''); ?>" href="/dashboard">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.products') ? 'active' : ''); ?>" href="/products">
                <i class="fas fa-box me-2"></i>Products
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.inventory') ? 'active' : ''); ?>" href="/inventory">
                <i class="fas fa-warehouse me-2"></i>Inventory
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.sales') ? 'active' : ''); ?>" href="/sales">
                <i class="fas fa-shopping-cart me-2"></i>Sales
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.customers') ? 'active' : ''); ?>" href="/customers">
                <i class="fas fa-users me-2"></i>Customers
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.suppliers') ? 'active' : ''); ?>" href="/suppliers">
                <i class="fas fa-truck me-2"></i>Suppliers
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.reports') ? 'active' : ''); ?>" href="/reports">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.settings') ? 'active' : ''); ?>" href="/settings">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenant.profile') ? 'active' : ''); ?>" href="/profile">
                <i class="fas fa-user me-2"></i>Profile
            </a>
            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
            <form action="/logout" method="POST" class="m-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link text-start w-100 border-0 bg-transparent">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>
        </nav>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/layouts/tenant.blade.php ENDPATH**/ ?>