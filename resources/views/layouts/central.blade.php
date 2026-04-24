<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meat Shop SaaS - @yield('title', 'Central Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    @env('local')
        <script src="https://cdn.tailwindcss.com"></script>
    @endenv
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        central: {
                            bg: '#eef2f7',
                            card: '#ffffff',
                            primary: '#f63470',
                            accent: '#ff8c57',
                        },
                    },
                    boxShadow: {
                        card: '0 8px 22px rgba(15, 23, 42, 0.1)',
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
        :root {
            --bg-1: #060202;
            --bg-2: #1a0808;
            --card: #ffffff;
            --card-strong: #ffffff;
            --card-border: #d6d9e0;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #f63470;
            --accent-2: #a41245;
            --line: #dbe3ef;
        }

        html,
        body {
            overflow-x: hidden;
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
            width: 100%;
            overflow-x: hidden;
            background: rgba(238, 242, 247, 0.96);
            border: 1px solid var(--line);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        }

        .central-sidebar {
            width: 272px;
            background:
                radial-gradient(circle at 18% -10%, rgba(246, 52, 112, 0.24), transparent 42%),
                radial-gradient(circle at 92% 10%, rgba(255, 140, 87, 0.16), transparent 36%),
                linear-gradient(168deg, #060202 0%, #1a0808 52%, #2f0b12 100%);
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.1);
            overflow: hidden;
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

        .central-header {
            backdrop-filter: blur(10px);
            background: rgba(245, 248, 252, 0.9);
            border-bottom: 1px solid #dbe3ef;
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

        .btn-danger-gradient {
            background: linear-gradient(90deg, #d32f2f, #e53935);
            color: #fff;
            border: 0;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px rgba(229, 57, 53, 0.18);
        }

        .btn-danger-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(229, 57, 53, 0.22);
        }

        .avatar-ring {
            background: linear-gradient(135deg, #ff9b8d 0%, var(--accent) 100%);
        }

        .central-brand-card {
            background: linear-gradient(160deg, rgba(164, 18, 69, 0.52), rgba(246, 52, 112, 0.24));
            border: 1px solid rgba(255, 255, 255, 0.26);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(8px);
            text-align: center;
        }

        .central-brand-logo {
            width: 148px;
            height: 148px;
            margin: 0 auto 0.8rem;
            padding: 0;
            border-radius: 22px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.36);
            box-shadow: 0 12px 28px rgba(28, 9, 14, 0.28);
            background: rgba(255, 255, 255, 0.08);
            filter: none;
        }

        .central-brand-fallback {
            width: 148px;
            height: 148px;
            margin: 0 auto 0.8rem;
            border-radius: 22px;
            border: 2px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.1);
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
        }

        .central-header button,
        .central-header a:not(.btn-primary-gradient) {
            border-color: rgba(255, 255, 255, 0.22) !important;
            background: rgba(255, 255, 255, 0.92) !important;
            color: #3f0f1a !important;
        }

        .central-header button:hover,
        .central-header a:not(.btn-primary-gradient):hover {
            background: #ffffff !important;
            color: #25070f !important;
        }

        .central-header h1 {
            color: #111827 !important;
        }

        .central-header p {
            color: #64748b !important;
        }

        .central-header .avatar-ring > div {
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
            color: #1f2937 !important;
        }

        main .text-slate-500,
        main .text-slate-400 {
            color: #64748b !important;
        }

        main .bg-indigo-50,
        main .bg-emerald-50,
        main .bg-amber-50,
        main .bg-rose-50,
        main .bg-teal-50,
        main .bg-slate-100,
        main .bg-slate-200 {
            background: #f8fafc !important;
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
            background: #f3f4f6 !important;
            color: inherit !important;
        }

        main .shadow-card,
        main .shadow-sm,
        main .shadow-md,
        main .shadow-lg,
        main .hover\:shadow-lg:hover,
        main .hover\:shadow-xl:hover {
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.1) !important;
        }

        main select,
        main select option,
        main select optgroup {
            background: #ffffff !important;
            color: #1f2937 !important;
        }

        main input:not([type="checkbox"]):not([type="radio"]),
        main textarea {
            border-color: #d5dce8 !important;
            background: #ffffff !important;
            color: #111827 !important;
        }

        main input:not([type="checkbox"]):not([type="radio"])::placeholder,
        main textarea::placeholder {
            color: #94a3b8 !important;
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
            -webkit-text-fill-color: #111827 !important;
            box-shadow: 0 0 0 1000px #ffffff inset !important;
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
            transition: background-color 5000s ease-in-out 0s;
            caret-color: #111827;
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
    @stack('styles')
</head>
<body class="antialiased">
    <div class="main-shell flex">
        <aside class="central-sidebar shrink-0 p-4 text-white lg:fixed lg:inset-y-0 lg:left-0 lg:h-screen" id="centralSidebar">
            <div class="central-brand-card mb-6 rounded-2xl p-4">
                <img src="{{ asset('im.png') }}" alt="MeatShop Central Logo" class="central-brand-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="central-brand-fallback" aria-hidden="true">MS</span>
                <p class="heading-font mb-0 text-lg font-semibold tracking-tight">MeatShop Central</p>
                <p class="mb-2 text-xs text-white/80">SaaS Operations Hub</p>
                @php
                    $displayName = session('user.name');
                    $sessionUserId = session('user.id');

                    if ($sessionUserId) {
                        $displayName = \App\Models\User::query()->whereKey($sessionUserId)->value('name') ?? $displayName;
                    }
                @endphp
                @if($displayName)
                    <p class="mb-0 text-base text-white/85">{{ $displayName }}</p>
                @endif
            </div>

            <nav class="space-y-2">
                <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="{{ route('dashboard') }}">
                    <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                    Dashboard
                </a>
                <a class="nav-item {{ request()->routeIs('tenants.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="{{ route('tenants.index') }}">
                    <i data-lucide="building-2" class="h-4 w-4"></i>
                    Tenants
                </a>
                <a class="nav-item {{ request()->routeIs('subscription.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="{{ route('subscription.billing') }}">
                    <i data-lucide="credit-card" class="h-4 w-4"></i>
                    Billing
                </a>
                <a class="nav-item {{ request()->routeIs('pricing') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="{{ route('pricing') }}">
                    <i data-lucide="badge-dollar-sign" class="h-4 w-4"></i>
                    Plans
                </a>
                <a class="nav-item {{ request()->routeIs('admin.versions.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="{{ route('admin.versions.index') }}">
                    <i data-lucide="package" class="h-4 w-4"></i>
                    Versions
                </a>
                <a class="nav-item {{ request()->routeIs('tenants.create') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium" href="{{ route('tenants.create') }}">
                    <i data-lucide="plus-circle" class="h-4 w-4"></i>
                    Create Tenant
                </a>
            </nav>

            <div class="mt-6 border-t border-white/20 pt-4">
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="nav-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1 lg:ml-[272px]">
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
                        @yield('header_actions')
                        <a href="{{ route('tenants.create') }}" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            <span class="hidden sm:inline">Create Tenant</span>
                        </a>
                        <div class="avatar-ring inline-flex h-10 w-10 items-center justify-center rounded-full p-[1px]">
                            <div class="inline-flex h-full w-full items-center justify-center rounded-full bg-white text-sm font-semibold text-slate-700">
                                {{ strtoupper(substr(session('user.name', 'U'), 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="min-w-0 max-w-full overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8">
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
            document.getElementById('centralSidebar')?.classList.toggle('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
