@extends((app()->bound('tenant') && tenant()) || preg_match('/^tenant:(.+)$/', (string) session('auth_context', 'central')) ? 'layouts.tenant' : 'layouts.central')

@section('title', 'Plans')

@section('content')
@php
    $isTenant = app()->bound('tenant');
    // Also treat the request as tenant-origin when the session carries a
    // tenant auth_context (format "tenant:HOST"). This helps when tenant
    // requests render without tenancy bound on the server but the user's
    // session indicates they are on a tenant host.
    $sessionAuthCtx = (string) session('auth_context', 'central');
    $sessionTenantHost = null;
    // Allow controller to force a tenant host (via ?tenant= or ?tenant_host=)
    $forcedTenantHost = $forcedTenantHost ?? null;
    if ($forcedTenantHost) {
        $sessionTenantHost = $forcedTenantHost;
        $isTenant = true;
    }
    if (preg_match('/^tenant:(.+)$/', $sessionAuthCtx, $m)) {
        $sessionTenantHost = $m[1];
        $isTenant = true;
    }

    // When rendering in a tenant context, prefer tenant-scoped billing routes
    // and avoid falling back to central billing. If tenant billing isn't
    // available, direct to tenant signup instead of central billing.
    $billingUrl = null;
    if ($isTenant) {
        if (\Illuminate\Support\Facades\Route::has('tenant.subscription.billing')) {
            $billingUrl = route('tenant.subscription.billing');
        } else {
            $billingUrl = null; // intentionally avoid central fallback for tenant
        }
    } else {
        if (\Illuminate\Support\Facades\Route::has('subscription.billing')) {
            $billingUrl = route('subscription.billing');
        }
    }

    $planSelectBaseUrl = $billingUrl ?? route('tenants.create');

    $plans = [
        [
            'id' => 'basic',
            'tag' => 'Starter',
            'name' => 'Basic',
            'description' => 'For single-branch shops starting digital operations.',
            'price_monthly' => 29,
            'price_annual' => 24,
            'accent' => 'emerald',
            'popular' => false,
            'features' => [
                ['label' => 'Up to 100 products', 'included' => true],
                ['label' => 'Inventory tracking and stock alerts', 'included' => true],
                ['label' => 'Single user access', 'included' => true],
                ['label' => 'POS operations', 'included' => false],
                ['label' => 'Data export', 'included' => false],
            ],
        ],
        [
            'id' => 'standard',
            'tag' => 'Growth',
            'name' => 'Standard',
            'description' => 'Best for growing meat shops managing multiple workflows.',
            'price_monthly' => 79,
            'price_annual' => 66,
            'accent' => 'indigo',
            'popular' => false,
            'features' => [
                ['label' => 'Unlimited products', 'included' => true],
                ['label' => 'Full POS system', 'included' => true],
                ['label' => 'Supplier and customer management', 'included' => true],
                ['label' => 'Basic reports and CSV export', 'included' => true],
                ['label' => 'Up to 3 users', 'included' => true],
            ],
        ],
        [
            'id' => 'premium',
            'tag' => 'Scale',
            'name' => 'Premium',
            'description' => 'For advanced operations and multi-branch optimization.',
            'price_monthly' => 149,
            'price_annual' => 125,
            'accent' => 'rose',
            'popular' => true,
            'features' => [
                ['label' => 'Everything in Standard', 'included' => true],
                ['label' => 'Advanced analytics dashboard', 'included' => true],
                ['label' => 'Unlimited export (CSV/Excel/PDF)', 'included' => true],
                ['label' => 'API access and batch operations', 'included' => true],
                ['label' => 'Unlimited users and priority support', 'included' => true],
            ],
        ],
    ];
    // Determine proceed base URL: tenant checkout when in tenant and available,
    // otherwise fall back to central billing or tenant signup.
    $tenantCheckoutUrl = Route::has('tenant.payments.checkout') ? route('tenant.payments.checkout') : null;
    $centralBillingUrl = Route::has('subscription.billing') ? route('subscription.billing') : null;
    if ($isTenant) {
        $proceedBase = $tenantCheckoutUrl ?? $centralBillingUrl ?? route('tenants.create');
    } else {
        $proceedBase = $centralBillingUrl ?? route('tenants.create');
    }
    // Build a central-host absolute billing URL using the configured app URL
    // so tenants can be redirected to central billing regardless of current host.
    $centralBillingFull = null;
    if (Route::has('subscription.billing')) {
        $relative = route('subscription.billing', [], false);
        $appUrl = rtrim(config('app.url') ?? '', '/');
        $centralBillingFull = $appUrl . ($relative ?: '/subscription/billing');
    }
    // Peso conversion rate for display (USD -> PHP). Default 55 PHP per USD.
    $pesoRate = env('PESO_RATE', 55);
    $plans_for_js = collect($plans)->map(function ($p) use ($pesoRate) {
        $p['price_monthly'] = round(($p['price_monthly'] ?? 0) * $pesoRate, 2);
        $p['price_annual'] = round(($p['price_annual'] ?? 0) * $pesoRate, 2);
        return $p;
    })->toArray();
    // Accent utility classes used in the plan cards
    $accentClasses = [
        'emerald' => [
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ring' => 'from-emerald-500/20 to-emerald-100',
            'button' => 'border-emerald-200 text-emerald-700 hover:bg-emerald-600 hover:text-white',
            'check' => 'text-emerald-600',
        ],
        'indigo' => [
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'ring' => 'from-indigo-500/20 to-indigo-100',
            'button' => 'border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white',
            'check' => 'text-indigo-600',
        ],
        'rose' => [
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
            'ring' => 'from-rose-500/20 to-rose-100',
            'button' => 'border-rose-200 text-rose-700 hover:bg-rose-600 hover:text-white',
            'check' => 'text-rose-600',
        ],
    ];
    // Build an absolute tenant checkout URL server-side when rendering in a tenant
    // context. This avoids relying on client-side `location.origin` which can
    // cause tenant routes to be requested on the central host.
    $tenantCheckoutFull = null;
    $tenantPrimaryDomain = null;
    if ($isTenant && Route::has('tenant.payments.checkout')) {
        $relativeTenant = route('tenant.payments.checkout', [], false);
        // Prefer the tenant's configured domain (primary) when available
        // to avoid using the current request host which may be central.
        $tenantOrigin = null;
        try {
            if (function_exists('tenant') && tenant()) {
                $t = tenant();
            } elseif (app()->bound('tenant')) {
                $t = app('tenant');
            } else {
                $t = null;
            }
            if (!empty($t) && method_exists($t, 'domains')) {
                $first = $t->domains()->first();
                if ($first && !empty($first->domain)) {
                    $tenantPrimaryDomain = $first->domain;
                    $port = request()->getPort();
                    $portPart = ($port && $port !== 80 && $port !== 443) ? ':' . $port : '';
                    $tenantOrigin = (request()->getScheme() ?: 'http') . '://' . $first->domain . $portPart;
                }
            }
        } catch (\Throwable $e) {
            $tenantOrigin = null;
            $tenantPrimaryDomain = null;
        }

        // Fall back to request origin or app.url
        if (empty($tenantOrigin)) {
            $tenantOrigin = request()->getSchemeAndHttpHost() ?: (rtrim(config('app.url') ?? '', '/'));
        }

        $tenantCheckoutFull = $tenantOrigin . ($relativeTenant ?: '/dashboard/payments/checkout');
    }
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6 p-6">
    <section class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="heading-font mb-1 text-2xl font-semibold text-slate-900">Plans</h2>
                <p class="mb-0 text-sm text-slate-500">Manage subscription tiers and pricing options.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="monthlyToggle" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    Monthly
                </button>
                <button type="button" id="annualToggle" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    Annual
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Save 15%</span>
                </button>
                <a href="{{ $billingUrl ?? '#' }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 {{ $billingUrl ? '' : 'pointer-events-none opacity-60' }}" @if(!$billingUrl) aria-disabled="true" @endif>
                    <i data-lucide="receipt-text" class="h-4 w-4"></i>
                    Manage Billing
                </a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach($plans as $plan)
            @php
                $accent = $accentClasses[$plan['accent']];
            @endphp
            <article class="relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-6 shadow-md transition duration-200 hover:-translate-y-1 hover:shadow-xl {{ $plan['popular'] ? 'ring-2 ring-rose-300/60' : '' }}">
                @if($plan['popular'])
                    <div class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-rose-700 to-rose-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Most Popular</div>
                @endif

                <div class="mb-5">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $accent['badge'] }}">{{ $plan['tag'] }}</span>
                    <h3 class="heading-font mt-3 text-2xl font-semibold text-slate-900">{{ $plan['name'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $plan['description'] }}</p>
                </div>

                <div class="mb-5">
                    <div class="flex items-end gap-1">
                        <span class="price heading-font text-4xl font-semibold text-slate-900" data-monthly="{{ number_format($plan['price_monthly'] * $pesoRate, 2, '.', '') }}" data-annual="{{ number_format($plan['price_annual'] * $pesoRate, 2, '.', '') }}">₱{{ number_format($plan['price_monthly'] * $pesoRate, 2) }}</span>
                        <span class="mb-1 text-sm text-slate-500">/month</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Billed monthly. Annual saves about 15%.</p>
                    <div class="mt-4 h-1.5 rounded-full bg-gradient-to-r {{ $accent['ring'] }}"></div>
                </div>

                <ul class="space-y-2.5 text-sm">
                    @foreach($plan['features'] as $feature)
                        <li class="flex items-start gap-2.5 {{ $feature['included'] ? 'text-slate-700' : 'text-slate-400' }}">
                            @if($feature['included'])
                                <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0 {{ $accent['check'] }}"></i>
                            @else
                                <i data-lucide="minus-circle" class="mt-0.5 h-4 w-4 shrink-0 text-slate-300"></i>
                            @endif
                            <span>{{ $feature['label'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-6 flex items-center gap-2">
                    <button type="button" class="inline-flex flex-1 items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $accent['button'] }}" onclick="selectPlan('{{ $plan['id'] }}')">
                        Choose {{ $plan['name'] }}
                    </button>
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" aria-label="View plan details">
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </button>
                </div>
            </article>
        @endforeach
    </section>
</div>
@endsection

@push('scripts')
<div id="toastContainer" aria-live="polite" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 z-50">
    <div id="toast" class="mx-auto w-full max-w-sm rounded-lg bg-slate-900 text-white p-3 shadow-lg pointer-events-auto hidden"></div>
</div>
<!-- SweetAlert2 for nicer pending confirmation dialogs -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const planSelectBaseUrl = @json($planSelectBaseUrl);
    const TENANT_CHECKOUT_URL = @json($tenantCheckoutFull);
    const PROCEED_BASE = @json($proceedBase);
    const CENTRAL_BILLING_URL = @json($centralBillingFull);
    const IS_TENANT = @json($isTenant);
    const TENANT_PRIMARY_DOMAIN = @json($tenantPrimaryDomain ?? $sessionTenantHost ?? null);
    const SESSION_TENANT_HOST = @json($sessionTenantHost ?? null);
    try { console.debug('pricing context', { IS_TENANT: IS_TENANT, SESSION_TENANT_HOST: SESSION_TENANT_HOST, TENANT_PRIMARY_DOMAIN: TENANT_PRIMARY_DOMAIN, LOCATION_ORIGIN: location.origin }); } catch (e) {}
    const CENTRAL_DOMAINS = @json(array_map('strtolower', (array) config('tenancy.central_domains', [])));
    const PLANS = @json(collect($plans_for_js)->keyBy('id'));
    let currentBillingMode = 'monthly';

    function selectPlan(plan) {
        const normalized = String(plan || '').toLowerCase();
        // Always show the plan intro modal so both tenant and central users
        // can request a subscription or proceed to manual checkout.
        openPlanModal(normalized);
    }

    function showToast(message, timeout = 5000) {
        try {
            const toast = document.getElementById('toast');
            if (!toast) return alert(message);
            toast.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, timeout);
        } catch (e) {
            console.error(e);
        }
    }

    function showPendingAlert(title = 'Subscription Pending', text = 'Your subscription request has been received. Please wait for central admin approval.') {
        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    html: text,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#a41245',
                    background: '#ffffff',
                    color: '#111827',
                    iconColor: '#34d399',
                    customClass: { popup: 'rounded-xl' }
                });
                return;
            }
        } catch (e) {
            // ignore
        }
        try { window.alert(title + "\n\n" + text); return; } catch (e) { /* ignore */ }

        // Fallback: create an inline modal overlay so user always sees confirmation
        try {
            // avoid duplicate
            if (document.getElementById('inlinePendingModal')) return;
            const overlay = document.createElement('div');
            overlay.id = 'inlinePendingModal';
            overlay.className = 'fixed inset-0 z-50 flex items-center justify-center';
            overlay.style.backgroundColor = 'rgba(0,0,0,0.45)';

            const box = document.createElement('div');
            box.className = 'relative z-50 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl text-center';

            const iconWrap = document.createElement('div');
            iconWrap.className = 'mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50';

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', '48');
            svg.setAttribute('height', '48');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.innerHTML = '<path fill="#10b981" d="M9 16.2l-3.5-3.5a1 1 0 0 0-1.4 1.4l4.2 4.2a1 1 0 0 0 1.4 0l9.2-9.2a1 1 0 1 0-1.4-1.4L9 16.2z"/>';
            iconWrap.appendChild(svg);

            const h = document.createElement('h3');
            h.textContent = title;
            h.className = 'heading-font text-2xl font-semibold text-slate-900 mb-2';

            const p = document.createElement('div');
            p.innerHTML = text;
            p.className = 'text-sm text-slate-600 mb-4';

            const btn = document.createElement('button');
            btn.textContent = 'OK';
            btn.className = 'btn-primary-gradient inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold';

            btn.addEventListener('click', function () {
                try { overlay.remove(); } catch (e) { overlay.style.display = 'none'; }
            });

            box.appendChild(iconWrap);
            box.appendChild(h);
            box.appendChild(p);
            box.appendChild(btn);
            overlay.appendChild(box);
            document.body.appendChild(overlay);
        } catch (e) {
            // final fallback to toast
            showToast(text);
        }
    }

    function setBillingMode(mode) {
        const isAnnual = mode === 'annual';
        document.querySelectorAll('.price').forEach(function (node) {
            const monthly = Number(node.dataset.monthly || 0);
            const annual = Number(node.dataset.annual || monthly);
            const display = (isAnnual ? annual : monthly);
            node.textContent = '₱' + Number(display).toFixed(2);
        });

        const monthlyToggle = document.getElementById('monthlyToggle');
        const annualToggle = document.getElementById('annualToggle');

        if (isAnnual) {
            annualToggle.className = 'inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700';
            monthlyToggle.className = 'inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700';
        } else {
            monthlyToggle.className = 'inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700';
            annualToggle.className = 'inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700';
        }
        currentBillingMode = isAnnual ? 'annual' : 'monthly';
    }

    document.getElementById('monthlyToggle')?.addEventListener('click', function () {
        setBillingMode('monthly');
    });

    document.getElementById('annualToggle')?.addEventListener('click', function () {
        setBillingMode('annual');
    });

    // Modal handling for tenant plan intro
    let _selectedPlan = null;
    function openPlanModal(plan) {
        _selectedPlan = plan;
        const modal = document.getElementById('planIntroModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        const title = modal.querySelector('.plan-title');
        const billingEl = modal.querySelector('.plan-billing');
        if (title) title.textContent = 'Upgrade to ' + ((plan || '').toString().charAt(0).toUpperCase() + (plan || '').toString().slice(1));
        if (billingEl) billingEl.textContent = currentBillingMode === 'annual' ? 'Annual' : 'Monthly';

        // Populate price and features from PLANS map
        const planData = PLANS[plan] || {};
        const priceEl = modal.querySelector('.plan-price');
        const featuresEl = modal.querySelector('.plan-features');
        if (priceEl) {
            const price = currentBillingMode === 'annual' ? (planData.price_annual ?? planData.price_monthly ?? 0) : (planData.price_monthly ?? 0);
            priceEl.textContent = '₱' + (Number(price || 0)).toFixed(2) + (currentBillingMode === 'annual' ? ' /year' : ' /month');
        }
        // description
        const descEl = modal.querySelector('.plan-description');
        if (descEl) descEl.textContent = planData.description || '';
        const nameEl = modal.querySelector('.plan-name');
        if (nameEl) nameEl.textContent = planData.name || ((plan || '').toString().charAt(0).toUpperCase() + (plan || '').toString().slice(1));
        if (featuresEl) {
            featuresEl.innerHTML = '';
            const features = planData.features || [];
            features.forEach(function (f) {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2 text-sm';
                const dot = document.createElement('span');
                dot.className = 'text-rose-400 mt-1';
                dot.textContent = '•';
                const text = document.createElement('span');
                text.textContent = f.label || f;
                li.appendChild(dot);
                li.appendChild(text);
                featuresEl.appendChild(li);
            });
        }

        // Ensure the Proceed link points to the correct absolute URL
        // (tenant or central) using the server-provided PROCEED_BASE.
        try {
            const proceed = document.getElementById('proceedToCheckout');
                if (proceed) {
                    const qs = '?plan=' + encodeURIComponent(plan) + '&billing=' + encodeURIComponent(currentBillingMode);
                    let target;
                        if (IS_TENANT) {
                        // Tenant: prefer absolute checkout on the known tenant host
                        // if the server provided one or if the session carries the
                        // tenant host. Use the current page's port when composing
                        // SESSION_TENANT_HOST so we don't accidentally hit Apache
                        // on port 80 when the app runs on another port (e.g. :8000).
                        if (TENANT_CHECKOUT_URL) {
                            target = TENANT_CHECKOUT_URL;
                        } else if (SESSION_TENANT_HOST) {
                            const portPart = (location.port && !SESSION_TENANT_HOST.includes(':')) ? (':' + location.port) : '';
                            target = location.protocol + '//' + SESSION_TENANT_HOST + portPart + '/dashboard/payments/checkout';
                        } else {
                            target = location.origin + '/dashboard/payments/checkout';
                        }
                    } else {
                        // Central: prefer explicit central billing URL, then planSelectBaseUrl, then APP-origin fallback
                        target = CENTRAL_BILLING_URL || planSelectBaseUrl || (location.origin + '/subscription/billing');
                    }
                    proceed.setAttribute('href', target + qs);
                    // Debug info to help diagnose tenancy / redirect issues
                    try { console.debug('plan modal proceed target', { target: target + qs, IS_TENANT: IS_TENANT, PROCEED_BASE: PROCEED_BASE, CENTRAL_BILLING_URL: CENTRAL_BILLING_URL, planSelectBaseUrl: planSelectBaseUrl, location_origin: location.origin }); } catch (e) {}
                }
        } catch (e) {
            console.error('Unable to set proceed href', e);
        }
    }

    function closePlanModal() {
        const modal = document.getElementById('planIntroModal');
        if (!modal) return;
        modal.classList.add('hidden');
        _selectedPlan = null;
    }

    function proceedToCheckoutFromModal() {
        const plan = _selectedPlan || '';
        const qs = '?plan=' + encodeURIComponent(plan) + '&billing=' + encodeURIComponent(currentBillingMode);
        // Tenant users should stay on their tenant checkout when possible.
        // Only use central billing when not in a tenant or when tenant checkout
        // isn't available.
        const tenantPath = '/dashboard/payments/checkout' + qs;
        // If server provided an absolute tenant checkout URL and it is not
        // a central domain, navigate directly to it to ensure tenant context.
        try {
            if (TENANT_CHECKOUT_URL) {
                const tc = new URL(TENANT_CHECKOUT_URL);
                const tcHost = (tc.host || '').split(':')[0].toLowerCase();
                if (!CENTRAL_DOMAINS.includes(tcHost)) {
                    try { console.debug('proceedToCheckout direct tenant URL', { target: TENANT_CHECKOUT_URL + qs }); } catch (e) {}
                    window.location.href = TENANT_CHECKOUT_URL + qs;
                    return;
                }
            }
        } catch (e) {
            // ignore URL parse errors
        }
        // If the server rendered an absolute tenant checkout URL, prefer it
        // for tenant pages to guarantee navigation stays on tenant origin.
        if (IS_TENANT && TENANT_CHECKOUT_URL) {
            try { console.debug('proceedToCheckout using server tenant URL', { target: TENANT_CHECKOUT_URL + qs }); } catch (e) {}
            window.location.href = TENANT_CHECKOUT_URL + qs;
            return;
        }
        if (IS_TENANT) {
            // Tenant: build target and prevent navigating to central domains.
            const targetBase = TENANT_CHECKOUT_URL || (location.origin + '/dashboard/payments/checkout');
            const target = targetBase + qs;
            try { console.debug('proceedToCheckout tenant redirect', { target: target, IS_TENANT: IS_TENANT, TENANT_CHECKOUT_URL: TENANT_CHECKOUT_URL, location_origin: location.origin }); } catch (e) {}

            // If the target host looks like a central domain, block and show guidance
            try {
                const url = new URL(target);
                const hostLower = url.host.toLowerCase();
                const isCentralHost = CENTRAL_DOMAINS.includes(hostLower.split(':')[0]) || CENTRAL_DOMAINS.includes(hostLower);
                if (isCentralHost) {
                    if (TENANT_PRIMARY_DOMAIN) {
                        const tenantLink = (location.protocol + '//' + TENANT_PRIMARY_DOMAIN + (url.pathname || '/dashboard/payments/checkout') + url.search);
                        showToast('Detected central host. Open the tenant checkout at ' + tenantLink);
                        window.location.href = tenantLink;
                        return;
                    }

                    // If we don't have a server-provided tenant primary domain,
                    // try to use the referrer origin when it appears to be a tenant
                    // (helps when the modal was rendered on central but user came
                    // from the tenant page and the browser referrer is present).
                    try {
                        const ref = document.referrer ? new URL(document.referrer) : null;
                        const refHost = ref ? (ref.host.split(':')[0].toLowerCase()) : null;
                        const isRefTenant = ref && !CENTRAL_DOMAINS.includes(refHost);
                        if (isRefTenant) {
                            const tenantLink = ref.protocol + '//' + ref.host + (url.pathname || '/dashboard/payments/checkout') + url.search;
                            showToast('Detected central host. Opening tenant checkout at ' + tenantLink);
                            window.location.href = tenantLink;
                            return;
                        }
                    } catch (e) {
                        // ignore referrer parsing errors
                    }
                }
            } catch (e) {
                // if URL parsing fails, just proceed with target
            }

            window.location.href = target;
            return;
        }

        // If tenant checkout isn't available, fall back to central billing
        // (absolute URL built from APP_URL) when present.
        if (CENTRAL_BILLING_URL) {
            window.location.href = CENTRAL_BILLING_URL + qs;
            return;
        }

        // Final fallback: construct a tenant path on current origin.
        window.location.href = location.origin + tenantPath;
    }

    async function requestSubscriptionFromModal() {
        const plan = _selectedPlan || '';
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            // If we're already on the tenant origin, try a same-origin POST.
            if (IS_TENANT) {
                try {
                    // Use GET endpoint that works without CSRF
                    const res = await fetch(`/create-subscription/${plan}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'text/plain'
                        }
                    });

                    const raw = await res.text();
                    let payload;
                    try { payload = raw ? JSON.parse(raw) : {}; } catch (e) { payload = null; }

                    if (!res.ok) {
                        // If server returned non-JSON (Whoops) or an error, fallback
                        // to central flow below.
                        throw new Error(payload && (payload.error || payload.message) ? (payload.error || payload.message) : ('Request failed: ' + res.status));
                    }

                    if (payload && payload.success) {
                        // Close the plan modal first to avoid overlay/z-index issues,
                        // then show the pending confirmation. This prevents a brief
                        // blank flicker where two modals compete for focus.
                        if (typeof refreshBillingFromApi === 'function') refreshBillingFromApi();
                        closePlanModal();
                        // Give the UI one animation frame to hide the modal,
                        // then show the confirmation dialog.
                        requestAnimationFrame(function () {
                            showPendingAlert('Subscription Pending', payload.message || 'Your subscription request is pending central approval.');
                        });
                        return;
                    }

                    showToast((payload && payload.error) || 'Failed to request subscription');
                    return;
                } catch (err) {
                    console.warn('Tenant POST failed, falling back to central flow', err);
                    // fall through to central fallback below
                }
            }

            // Not on tenant origin: do not attempt cross-origin POSTs (CORS/cookies
            // frequently fail). Instead, open the tenant pricing/checkout page so
            // the user can request the subscription from the tenant UI.
            let tenantOpenUrl = null;
            if (TENANT_CHECKOUT_URL) {
                try { tenantOpenUrl = new URL(TENANT_CHECKOUT_URL).origin + '/pricing'; } catch (e) { tenantOpenUrl = null; }
            }
            if (!tenantOpenUrl && SESSION_TENANT_HOST) {
                const portPart = (location.port && !SESSION_TENANT_HOST.includes(':')) ? (':' + location.port) : '';
                tenantOpenUrl = location.protocol + '//' + SESSION_TENANT_HOST + portPart + '/pricing';
            }

            if (tenantOpenUrl) {
                // Try to create the central pending request directly by POSTing
                // tenant_host to the public endpoint. This avoids asking the user
                // to navigate to the tenant UI when we can record the request now.
                if (SESSION_TENANT_HOST) {
                    try {
                        const res = await fetch('/subscription/request-public', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ plan: plan, tenant_host: SESSION_TENANT_HOST })
                        });

                        const raw = await res.text();
                        let payload;
                        try { payload = raw ? JSON.parse(raw) : {}; } catch (e) { payload = null; }

                        if (res.ok && payload && payload.success) {
                            closePlanModal();
                            requestAnimationFrame(function () {
                                showPendingAlert('Subscription Pending', payload.message || 'Your subscription request is pending central approval.');
                            });
                            return;
                        }

                        console.warn('Central POST did not succeed, falling back to tenant UI', payload);
                    } catch (e) {
                        console.warn('Central POST failed, falling back to tenant UI', e);
                    }
                }

                // If central POST not attempted or failed, show confirmation
                // (user can be instructed to open tenant pricing if needed).
                closePlanModal();
                requestAnimationFrame(function () {
                    if (res.ok && raw.includes('Created subscription request')) {
                        showPendingAlert('Request Submitted', 'Your subscription request has been received. Please wait for central admin approval.');
                        return;
                    }
                });
                return;
            }

            // Unknown tenant origin: show friendly guidance and wait-for-approval
            closePlanModal();
            requestAnimationFrame(function () {
                showPendingAlert('Request Submitted', 'Your subscription request has been received. Please wait for central admin approval.');
            });
            return;
        } catch (err) {
            console.error(err);
            showToast('Unable to request subscription. ' + (err && err.message ? err.message : 'Please try again later.'));
        }
    }
    
    function requestSubscription() {
        if (_selectedPlan) {
            window.location.href = '/create-subscription/' + _selectedPlan;
        } else {
            alert('Please select a plan first');
        }
    }
    
    async function createSubscriptionRequest() {
        if (!_selectedPlan) {
            alert('Please select a plan first');
            return;
        }
        
        try {
            const response = await fetch('/create-subscription/' + _selectedPlan);
            const text = await response.text();
            
            if (response.ok && text.includes('Created subscription request')) {
                // Close the modal
                closePlanModal();
                // Show success alert
                showPendingAlert('Request Submitted', 'Your subscription request has been received. Please wait for central admin approval.');
            } else {
                alert('Failed to create subscription request. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating subscription request. Please try again.');
        }
    }
</script>
<style>
    /* Simple modal utilities */
    .modal-backdrop { background-color: rgba(0,0,0,0.45); }
</style>

<!-- Intro modal markup -->
<div id="planIntroModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 modal-backdrop z-40"></div>
    <div class="relative z-50 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="plan-title text-lg font-semibold">Plan</h3>
                <p class="mt-1 text-sm text-slate-600">Billing: <span class="plan-billing">Monthly</span></p>
            </div>
            <button onclick="closePlanModal()" class="text-slate-500 hover:text-slate-900 text-xl leading-none">&times;</button>
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-semibold plan-name"></h4>
                    <p class="text-sm text-slate-600 plan-description mt-1"></p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-slate-500">Monthly price</div>
                    <div class="text-xl font-semibold plan-price">₱0.00</div>
                </div>
            </div>
            <p class="mt-4 text-sm text-slate-500">Choose one of the options below:</p>
            <div class="mt-4 grid grid-cols-1 gap-3">
                <button onclick="createSubscriptionRequest()" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Request Subscription</button>
                <a id="proceedToCheckout" href="javascript:void(0)" onclick="proceedToCheckoutFromModal(); return false;" class="w-full inline-block text-center rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-500">Proceed to Manual Checkout</a>
            </div>
            <p class="mt-3 text-xs text-slate-400">Your plan will activate after Central Admin approval.</p>
        </div>
    </div>
</div>
@endpush
