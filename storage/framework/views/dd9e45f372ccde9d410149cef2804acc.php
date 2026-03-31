<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Meat Shop SaaS - <?php echo $__env->yieldContent('title', 'Central Dashboard'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        central: {
                            bg: '#f8fafc',
                            card: '#ffffff',
                            primary: '#1e3a8a',
                            accent: '#0d9488',
                        },
                    },
                    boxShadow: {
                        card: '0 8px 30px rgba(15, 23, 42, 0.06)',
                    },
                    borderRadius: {
                        xl2: '1rem',
                    },
                },
            },
        };
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at 12% -5%, rgba(30, 58, 138, 0.08) 0, transparent 30%),
                radial-gradient(circle at 100% 0%, rgba(13, 148, 136, 0.08) 0, transparent 24%),
                #f8fafc;
        }

        .heading-font {
            font-family: 'Poppins', sans-serif;
        }

        .main-shell {
            min-height: 100vh;
        }

        .central-sidebar {
            width: 272px;
            background: linear-gradient(170deg, rgba(30, 58, 138, 0.92) 0%, rgba(30, 64, 175, 0.9) 42%, rgba(13, 148, 136, 0.86) 100%);
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.08);
        }

        .nav-item {
            color: rgba(241, 245, 249, 0.85);
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateX(2px);
        }

        .nav-item.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.28);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.2);
        }

        .central-header {
            backdrop-filter: blur(10px);
            background: rgba(248, 250, 252, 0.86);
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #0f766e 100%);
            color: #fff;
            border: 0;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.25);
        }

        .btn-primary-gradient:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(30, 64, 175, 0.32);
        }

        .avatar-ring {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
        }

        .sidebar-toggle {
            display: none;
        }

        @media (max-width: 1024px) {
            .central-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }

            .central-sidebar.show {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: inline-flex;
            }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="text-slate-900 antialiased">
    <div class="main-shell flex">
        <aside class="central-sidebar shrink-0 p-4 text-white lg:sticky lg:top-0 lg:h-screen" id="centralSidebar">
            <div class="mb-6 rounded-2xl border border-white/20 bg-white/10 p-4">
                <div class="mb-2 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white">
                        <i data-lucide="store" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <p class="heading-font mb-0 text-base font-semibold tracking-tight">MeatShop Central</p>
                        <p class="mb-0 text-xs text-white/75">SaaS Operations Hub</p>
                    </div>
                </div>
                <?php
                    $displayName = session('user.name');
                    $sessionUserId = session('user.id');

                    if ($sessionUserId) {
                        $displayName = \App\Models\User::query()->whereKey($sessionUserId)->value('name') ?? $displayName;
                    }
                ?>
                <?php if($displayName): ?>
                    <p class="mb-0 text-sm text-white/75"><?php echo e($displayName); ?></p>
                <?php endif; ?>
            </div>

            <nav class="space-y-2">
                <a class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?> flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="<?php echo e(route('dashboard')); ?>">
                    <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                    Dashboard
                </a>
                <a class="nav-item <?php echo e(request()->routeIs('tenants.*') ? 'active' : ''); ?> flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="<?php echo e(route('tenants.index')); ?>">
                    <i data-lucide="building-2" class="h-4 w-4"></i>
                    Tenants
                </a>
                <a class="nav-item <?php echo e(request()->routeIs('subscription.*') ? 'active' : ''); ?> flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="<?php echo e(route('subscription.billing')); ?>">
                    <i data-lucide="credit-card" class="h-4 w-4"></i>
                    Billing
                </a>
                <a class="nav-item <?php echo e(request()->routeIs('pricing') ? 'active' : ''); ?> flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="<?php echo e(route('pricing')); ?>">
                    <i data-lucide="badge-dollar-sign" class="h-4 w-4"></i>
                    Plans
                </a>
                <a class="nav-item <?php echo e(request()->routeIs('tenants.create') ? 'active' : ''); ?> flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="<?php echo e(route('tenants.create')); ?>">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i>
                    Create Tenant
                </a>
            </nav>

            <div class="mt-6 border-t border-white/20 pt-4">
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="m-0">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="nav-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1">
            <header class="central-header sticky top-0 z-30 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button class="sidebar-toggle inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm lg:hidden" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <div>
                            <h1 class="heading-font mb-0 text-2xl font-semibold tracking-tight text-slate-900">MeatShop Central</h1>
                            <p class="mb-0 text-sm text-slate-500">Multi-tenant operations and billing overview</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <?php echo $__env->yieldContent('header_actions'); ?>
                        <a href="<?php echo e(route('tenants.create')); ?>" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            <span class="hidden sm:inline">Create Tenant</span>
                        </a>
                        <div class="avatar-ring inline-flex h-10 w-10 items-center justify-center rounded-full p-[1px]">
                            <div class="inline-flex h-full w-full items-center justify-center rounded-full bg-white text-sm font-semibold text-slate-700">
                                <?php echo e(strtoupper(substr(session('user.name', 'U'), 0, 1))); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.getElementById('centralSidebar')?.classList.toggle('show');
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Rusty\Music\Websys_Meatshop\resources\views/layouts/central.blade.php ENDPATH**/ ?>