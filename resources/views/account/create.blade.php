<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your MeatShop Account</title>

    @if (($showRecaptcha ?? false) && config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    @env('local')
        <script src="https://cdn.tailwindcss.com"></script>
    @endenv
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-1: #060202;
            --bg-2: #1a0808;
            --card: rgba(255, 255, 255, 0.04);
            --card-strong: rgba(255, 255, 255, 0.06);
            --card-border: rgba(255, 255, 255, 0.14);
            --text: #fef7f5;
            --muted: #d5b8b1;
            --accent: #f63470;
            --accent-2: #a41245;
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

        .theme-surface {
            border-color: var(--card-border) !important;
            background: var(--card) !important;
            box-shadow: 0 16px 38px rgba(0, 0, 0, 0.28);
        }

        .theme-block {
            border-color: var(--card-border) !important;
            background: var(--card-strong) !important;
        }

        .theme-input {
            border-color: rgba(255, 255, 255, 0.22) !important;
            background: rgba(255, 255, 255, 0.03) !important;
            color: #ffe9e5 !important;
        }

        .theme-input::placeholder {
            color: #c9a49b;
        }

        .theme-input:focus {
            border-color: rgba(246, 52, 112, 0.8) !important;
            --tw-ring-color: rgba(246, 52, 112, 0.22) !important;
        }

        .theme-action {
            background: linear-gradient(90deg, var(--accent-2), var(--accent)) !important;
            box-shadow: 0 10px 28px rgba(246, 52, 112, 0.28) !important;
        }

        .theme-action:hover {
            box-shadow: 0 14px 30px rgba(246, 52, 112, 0.35) !important;
        }

        .theme-icon {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ff6f9a !important;
        }

        .text-slate-900,
        .text-slate-700,
        .text-slate-600 {
            color: #ffe9e5 !important;
        }

        .text-slate-500,
        .text-slate-400 {
            color: var(--muted) !important;
        }

        .bg-emerald-100 {
            background: rgba(71, 215, 160, 0.14) !important;
        }

        .text-emerald-700 {
            color: #7be6be !important;
        }

        /* Themed select styling to blend dropdown with app theme */
        select.theme-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, rgba(255,255,255,0.12) 50%), linear-gradient(135deg, rgba(255,255,255,0.12) 50%, transparent 50%);
            background-position: calc(100% - 1rem) calc(50% - 6px), calc(100% - 0.5rem) calc(50% - 6px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 2.25rem !important;
            color: #ffe9e5 !important;
        }

        select.theme-input option {
            background: rgba(8,6,6,0.95);
            color: #ffe9e5;
        }

        /* Hide default IE/Edge dropdown arrow */
        select.theme-input::-ms-expand {
            display: none;
        }
    </style>
</head>
<body class="min-h-screen antialiased">
    @php
        $selectedPlan = old('plan', request('plan', 'basic'));
    @endphp

    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center p-4 sm:p-6 lg:p-8">
        <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="theme-surface hidden rounded-2xl border border-slate-200/70 bg-white/80 p-8 shadow-sm backdrop-blur lg:block">
                <div class="theme-icon mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                    <i data-lucide="store" class="h-6 w-6"></i>
                </div>
                <h1 class="heading-font text-3xl font-semibold leading-tight text-slate-900">Create Your MeatShop Account</h1>
                <p class="mt-2 text-sm text-slate-600">Set up your business and start managing your shop with centralized SaaS controls.</p>

                <div class="mt-8 space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </span>
                        <p>Provision tenant database and domain automatically.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </span>
                        <p>Assign initial plan and activate onboarding in minutes.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </span>
                        <p>Use central controls for lifecycle, billing, and notifications.</p>
                    </div>
                </div>
            </section>

            <section class="theme-surface rounded-2xl border border-slate-200/70 bg-white p-6 shadow-md sm:p-8">
                <div class="mb-6 text-center lg:text-left">
                    <div class="theme-icon mx-auto mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 lg:mx-0">
                        <i data-lucide="building-2" class="h-5 w-5"></i>
                    </div>
                    <h2 class="heading-font text-2xl font-semibold text-slate-900">Create Your MeatShop Account</h2>
                    <p class="mt-1 text-sm text-slate-500">Set up your business and start managing your shop.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="tenantSignupForm" action="{{ route('tenants.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="theme-block rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Personal Info</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Juan Dela Cruz" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('name') border-rose-300 ring-rose-200 @enderror" required>
                                <p class="mt-1 text-xs text-slate-500">Primary contact for this onboarding request.</p>
                            </div>
                            <div>
                                <label for="business_email" class="mb-1 block text-sm font-medium text-slate-700">Business Email</label>
                                <input type="email" id="business_email" name="business_email" value="{{ old('business_email', old('email')) }}" placeholder="owner@yourshop.com" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('business_email') border-rose-300 ring-rose-200 @enderror" required>
                            </div>
                        </div>
                    </div>

                    <div class="theme-block rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Business Info</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="business_name" class="mb-1 block text-sm font-medium text-slate-700">Business Name</label>
                                <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" placeholder="MeatShop Downtown" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('business_name') border-rose-300 ring-rose-200 @enderror" required>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="business_phone" class="mb-1 block text-sm font-medium text-slate-700">Business Phone</label>
                                    <input type="text" id="business_phone" name="business_phone" value="{{ old('business_phone') }}" placeholder="0917 000 0000" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('business_phone') border-rose-300 ring-rose-200 @enderror">
                                </div>
                                <div>
                                    <label for="domain" class="mb-1 block text-sm font-medium text-slate-700">Domain / Subdomain</label>
                                    <input type="text" id="domain" name="domain" placeholder="ramcar.localhost" value="{{ old('domain') }}" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('domain') border-rose-300 ring-rose-200 @enderror">
                                    <p class="mt-1 text-xs text-slate-500">Auto-generated from business name. You can still edit it manually.</p>
                                </div>
                            </div>
                            <div>
                                <label for="business_address" class="mb-1 block text-sm font-medium text-slate-700">Business Address</label>
                                <input type="text" id="business_address" name="business_address" value="{{ old('business_address') }}" placeholder="Street, Barangay, City" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('business_address') border-rose-300 ring-rose-200 @enderror">
                            </div>
                        </div>
                    </div>

                    <div class="theme-block rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Admin Account</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="admin_name" class="mb-1 block text-sm font-medium text-slate-700">Administrator Name</label>
                                <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name', old('name')) }}" placeholder="Store Administrator" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('admin_name') border-rose-300 ring-rose-200 @enderror" required>
                            </div>
                            <div>
                                <label for="admin_email" class="mb-1 block text-sm font-medium text-slate-700">Administrator Email</label>
                                <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email', old('business_email', old('email'))) }}" placeholder="admin@yourshop.com" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('admin_email') border-rose-300 ring-rose-200 @enderror" required>
                            </div>
                            <div>
                                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                                  <input type="password" id="password" name="password" placeholder="Leave blank to auto-generate" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none ring-indigo-200 transition focus:border-indigo-500 focus:ring-2 @error('password') border-rose-300 ring-rose-200 @enderror">
                                  <p class="mt-1 text-xs text-slate-500">Optional: leave blank to auto-generate a secure password and send it via email.</p>
                            </div>
                        </div>
                    </div>

                    <div class="theme-block rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Initial Plan</h3>
                        <div class="relative">
                            <input type="hidden" id="plan" name="plan" value="{{ old('plan', request('plan', 'basic')) }}">
                            <button type="button" id="planToggle" class="theme-input h-11 w-full rounded-lg border border-slate-300 bg-transparent px-4 text-sm text-slate-700 outline-none text-left flex items-center justify-between" aria-haspopup="listbox" aria-expanded="false">
                                <span id="planLabel">{{ ucfirst(old('plan', request('plan', 'basic'))) }}</span>
                                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <ul id="planList" role="listbox" tabindex="-1" class="absolute z-50 mt-2 w-full rounded-lg border border-slate-700 bg-[#0b0606] shadow-lg p-2 hidden">
                                @foreach(['basic','standard','premium','enterprise'] as $planKey)
                                    @php $label = ucfirst($planKey); @endphp
                                    <li data-value="{{ $planKey }}" class="plan-item cursor-pointer rounded-md px-3 py-2 text-sm text-slate-200 hover:bg-[#2a0b12]">{{ $label }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @if (($showRecaptcha ?? false) && config('services.recaptcha.site_key'))
                        <div class="flex justify-center">
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                        </div>
                    @endif

                    <button id="submitButton" type="submit" class="theme-action inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-700 to-teal-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                        <i data-lucide="user-plus" class="h-4 w-4"></i>
                        <span id="submitLabel">Create Tenant</span>
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        const signupForm = document.getElementById('tenantSignupForm');
        const submitButton = document.getElementById('submitButton');
        const submitLabel = document.getElementById('submitLabel');
        const businessNameInput = document.getElementById('business_name');
        const domainInput = document.getElementById('domain');
        const domainRoot = @json(config('tenancy.fallback_domain', 'localhost'));

        let domainTouched = Boolean(domainInput?.value?.trim());

        function slugify(value) {
            return (value || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function normalizeRoot(value) {
            const root = (value || 'localhost').trim().toLowerCase();
            return root.length > 0 ? root : 'localhost';
        }

        function updateAutoDomain() {
            if (!businessNameInput || !domainInput || domainTouched) {
                return;
            }

            const slug = slugify(businessNameInput.value);
            domainInput.value = slug ? `${slug}.${normalizeRoot(domainRoot)}` : '';
        }

        domainInput?.addEventListener('input', function () {
            domainTouched = this.value.trim().length > 0;
        });

        domainInput?.addEventListener('blur', function () {
            if (this.value.trim() === '') {
                domainTouched = false;
                updateAutoDomain();
            }
        });

        businessNameInput?.addEventListener('input', updateAutoDomain);

        updateAutoDomain();

        signupForm?.addEventListener('submit', function () {
            submitButton.disabled = true;
            submitLabel.textContent = 'Creating Account...';
        });

        // Custom themed plan select
        (function () {
            const toggle = document.getElementById('planToggle');
            const list = document.getElementById('planList');
            const hidden = document.getElementById('plan');
            const label = document.getElementById('planLabel');

            if (!toggle || !list || !hidden || !label) return;

            function openList() {
                list.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }

            function closeList() {
                list.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }

            toggle.addEventListener('click', function (e) {
                if (list.classList.contains('hidden')) openList(); else closeList();
            });

            list.addEventListener('click', function (e) {
                const item = e.target.closest('.plan-item');
                if (!item) return;
                const val = item.getAttribute('data-value');
                hidden.value = val;
                label.textContent = item.textContent.trim();
                closeList();
            });

            // close on outside click
            document.addEventListener('click', function (e) {
                if (!toggle.contains(e.target) && !list.contains(e.target)) {
                    closeList();
                }
            });
        })();
    </script>
</body>
</html>
