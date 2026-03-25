<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Meat Shop SaaS - <?php echo $__env->yieldContent('title', 'Central Dashboard'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
        }

        .sidebar {
            height: 100vh;
            background: linear-gradient(160deg, #183153 0%, #0f4c81 55%, #1e7f6f 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }

        .sidebar .brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.82);
            padding: 0.95rem 1.4rem;
            transition: all 0.2s;
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
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
                <i class="fas fa-building"></i>
                <h5 class="mb-0">MeatShop Central</h5>
            </div>
            <?php if(session('user.name')): ?>
                <small class="text-white-50"><?php echo e(session('user.name')); ?></small>
            <?php endif; ?>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
                <i class="fas fa-chart-line me-2"></i>Dashboard
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('tenants.*') ? 'active' : ''); ?>" href="<?php echo e(route('tenants.index')); ?>">
                <i class="fas fa-store me-2"></i>Tenants
            </a>
            <a class="nav-link <?php echo e(request()->routeIs('subscription.*') ? 'active' : ''); ?>" href="<?php echo e(route('subscription.billing')); ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i>Billing
            </a>
            <a class="nav-link" href="<?php echo e(route('pricing')); ?>">
                <i class="fas fa-tags me-2"></i>Plans
            </a>
            <a class="nav-link" href="<?php echo e(route('tenants.create')); ?>">
                <i class="fas fa-plus-circle me-2"></i>Create Tenant
            </a>
            <hr class="my-2" style="border-color: rgba(255,255,255,0.2);">
            <form action="<?php echo e(route('logout')); ?>" method="POST" class="m-0">
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
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/layouts/central.blade.php ENDPATH**/ ?>