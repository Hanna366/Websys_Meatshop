<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MeatShop POS - @yield('title', 'Branch Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        central: {
                            bg: '#060202',
                            card: 'rgba(255, 255, 255, 0.04)',
                            primary: '#f63470',
                            accent: '#ff8c57',
                        },
                    },
                    boxShadow: {
                        card: '0 16px 38px rgba(0, 0, 0, 0.28)',
                    },
                    borderRadius: {
                        xl2: '1rem',
                    },
                },
            },
        };
    </script>

    <style>
        :root {
            --bg-1: #060202;
            --bg-2: #1a0808;
            --card: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 255, 255, 0.14);
            --text: #fef7f5;
            --muted: #d5b8b1;
            --accent: #f63470;
            --accent-2: #a41245;
            --line: rgba(255, 255, 255, 0.12);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 18% -10%, rgba(246, 52, 112, 0.28), transparent 38%),
                radial-gradient(circle at 92% 10%, rgba(255, 140, 87, 0.2), transparent 32%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 50%, #2f0b12);
        }

        .heading-font {
            font-family: 'Sora', sans-serif;
        }

        .main-shell {
            min-height: 100vh;
            background: rgba(8, 2, 2, 0.74);
            border: 1px solid var(--line);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.45);
        }

        .tenant-sidebar {
            width: 272px;
            background: linear-gradient(168deg, rgba(36, 6, 10, 0.94) 0%, rgba(92, 12, 36, 0.92) 52%, rgba(164, 18, 69, 0.9) 100%);
            backdrop-filter: blur(12px);
            border-right: 1px solid var(--line);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.05);
        }

        .nav-item {
            color: #f8d7cf;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateX(2px);
        }

        .nav-item.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(164, 18, 69, 0.9), rgba(246, 52, 112, 0.9));
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 12px 28px rgba(96, 10, 37, 0.36);
        }

        .tenant-header {
            backdrop-filter: blur(10px);
            background: rgba(13, 2, 4, 0.85);
            border-bottom: 1px solid var(--line);
        }

        .btn-primary-gradient {
            background: linear-gradient(90deg, var(--accent-2), var(--accent));
            color: #fff;
            border: 0;
            transition: all 0.2s ease;
            box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28);
        }

        .btn-primary-gradient:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(246, 52, 112, 0.35);
        }

        .avatar-ring {
            background: linear-gradient(135deg, #ff9b8d 0%, var(--accent) 100%);
        }

        .tenant-header button,
        .tenant-header a:not(.btn-primary-gradient) {
            border-color: var(--line) !important;
            background: rgba(255, 255, 255, 0.03) !important;
            color: #f6d5cf !important;
        }

        .tenant-header button:hover,
        .tenant-header a:not(.btn-primary-gradient):hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
        }

        .tenant-header h1 {
            color: var(--text) !important;
        }

        .tenant-header p {
            color: var(--muted) !important;
        }

        .tenant-header .avatar-ring > div {
            background: #220106;
            color: #ffdcd3;
        }

        main .bg-white,
        main .bg-white\/70,
        main .bg-white\/80,
        main .bg-white\/90,
        main .bg-slate-50,
        main .bg-slate-50\/60,
        main .bg-slate-50\/70,
        main [class*="bg-white/"] {
            background: var(--card) !important;
        }

        main .border-slate-200,
        main .border-slate-200\/70,
        main .border-slate-300,
        main .border-slate-100,
        main .border-white\/70,
        main .border-white\/80,
        main .border-white\/90,
        main [class*="border-white/"] {
            border-color: var(--card-border) !important;
        }

        main .text-slate-900,
        main .text-slate-950,
        main .text-slate-800,
        main .text-slate-700,
        main .text-slate-600 {
            color: #ffe9e5 !important;
        }

        main .text-slate-500,
        main .text-slate-400 {
            color: var(--muted) !important;
        }

        main .bg-indigo-50,
        main .bg-emerald-50,
        main .bg-amber-50,
        main .bg-rose-50,
        main .bg-teal-50,
        main .bg-slate-100,
        main .bg-slate-200 {
            background: rgba(255, 255, 255, 0.06) !important;
        }

        main .hover\:bg-indigo-50:hover,
        main .hover\:bg-indigo-50\/40:hover,
        main .hover\:bg-indigo-100:hover,
        main .hover\:bg-indigo-600:hover,
        main .hover\:bg-slate-50:hover,
        main .hover\:bg-slate-100:hover,
        main .hover\:bg-rose-50:hover,
        main .hover\:bg-emerald-50:hover,
        main .hover\:bg-amber-50:hover {
            background: rgba(255, 255, 255, 0.03) !important;
            color: inherit !important;
        }

        main .shadow-card,
        main .shadow-sm,
        main .shadow-md,
        main .shadow-lg,
        main .hover\:shadow-lg:hover,
        main .hover\:shadow-xl:hover {
            box-shadow: 0 16px 38px rgba(0, 0, 0, 0.28) !important;
        }

        main select,
        main select option,
        main select optgroup {
            background: rgba(22, 4, 8, 0.98) !important;
            color: #ffe9e5 !important;
        }

        main input:not([type="checkbox"]):not([type="radio"]),
        main textarea {
            border-color: rgba(255, 255, 255, 0.22) !important;
            background: rgba(255, 255, 255, 0.03) !important;
            color: #ffe9e5 !important;
        }

        main input:not([type="checkbox"]):not([type="radio"])::placeholder,
        main textarea::placeholder {
            color: #c9a49b !important;
            opacity: 1;
        }

        main input:not([type="checkbox"]):not([type="radio"]):focus,
        main textarea:focus {
            border-color: rgba(246, 52, 112, 0.75) !important;
            box-shadow: 0 0 0 2px rgba(246, 52, 112, 0.2) !important;
            outline: none;
        }

        main input:-webkit-autofill,
        main input:-webkit-autofill:hover,
        main input:-webkit-autofill:focus,
        main textarea:-webkit-autofill,
        main select:-webkit-autofill {
            -webkit-text-fill-color: #ffe9e5 !important;
            box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.03) inset !important;
            -webkit-box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.03) inset !important;
            transition: background-color 5000s ease-in-out 0s;
            caret-color: #ffe9e5;
        }

        main select:focus {
            border-color: rgba(246, 52, 112, 0.75) !important;
            box-shadow: 0 0 0 2px rgba(246, 52, 112, 0.2) !important;
        }

        main option:checked,
        main option:hover {
            background: linear-gradient(90deg, rgba(164, 18, 69, 0.95), rgba(246, 52, 112, 0.95)) !important;
            color: #fff !important;
        }

        main .hover\:-translate-y-0\.5:hover,
        main .hover\:-translate-y-1:hover,
        main .hover\:translate-x-1:hover {
            transform: none !important;
        }

        .sidebar-toggle {
            display: none;
        }

        @media (max-width: 1024px) {
            .tenant-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 50;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }

            .tenant-sidebar.show {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: inline-flex;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased">
    <div class="main-shell flex">
        <aside class="tenant-sidebar shrink-0 p-4 text-white lg:sticky lg:top-0 lg:h-screen" id="tenantSidebar">
            <div class="mb-6 rounded-2xl border border-white/20 bg-white/10 p-4">
                <div class="mb-2 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white">
                        <i data-lucide="store" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <p class="heading-font mb-0 text-base font-semibold tracking-tight">Branch POS</p>
                        <p class="mb-0 text-xs text-white/75">Tenant Workspace</p>
                    </div>
                </div>
                @if(tenant())
                    <p class="mb-0 text-sm text-white/75">{{ tenant()->business_name ?? tenant()->tenant_id }}</p>
                @elseif(session('user.name'))
                    <p class="mb-0 text-sm text-white/75">{{ session('user.name') }}</p>
                @endif
            </div>

            <nav class="space-y-2">
                <a class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/dashboard">
                    <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                    Dashboard
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.products') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/products">
                    <i data-lucide="package" class="h-4 w-4"></i>
                    Products
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.inventory') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/inventory">
                    <i data-lucide="warehouse" class="h-4 w-4"></i>
                    Inventory
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.sales') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/sales">
                    <i data-lucide="shopping-cart" class="h-4 w-4"></i>
                    Sales
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.customers') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/customers">
                    <i data-lucide="users" class="h-4 w-4"></i>
                    Customers
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.suppliers') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/suppliers">
                    <i data-lucide="truck" class="h-4 w-4"></i>
                    Suppliers
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.reports') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/reports">
                    <i data-lucide="bar-chart-3" class="h-4 w-4"></i>
                    Reports
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.settings') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/settings">
                    <i data-lucide="settings" class="h-4 w-4"></i>
                    Settings
                </a>
                <a class="nav-item {{ request()->routeIs('tenant.profile') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="/profile">
                    <i data-lucide="user-circle" class="h-4 w-4"></i>
                    Profile
                </a>
            </nav>

            <div class="mt-6 border-t border-white/20 pt-4">
                <form action="/logout" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="nav-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1">
            <header class="tenant-header sticky top-0 z-30 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button class="sidebar-toggle inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm lg:hidden" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <div>
                            <h1 class="heading-font mb-0 text-2xl font-semibold tracking-tight text-slate-900">@yield('page_title', 'Tenant Dashboard')</h1>
                            <p class="mb-0 text-sm text-slate-500">@yield('page_subtitle', 'Branch operations overview and daily POS activity')</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        @yield('header_actions')
                        <a href="/pricing" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
                            <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                            <span class="hidden sm:inline">Upgrade Plan</span>
                        </a>
                        <div class="avatar-ring inline-flex h-10 w-10 items-center justify-center rounded-full p-[1px]">
                            <div class="inline-flex h-full w-full items-center justify-center rounded-full bg-white text-sm font-semibold text-slate-700">
                                {{ strtoupper(substr(session('user.name', 'U'), 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.getElementById('tenantSidebar')?.classList.toggle('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
