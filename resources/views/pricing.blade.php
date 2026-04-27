@extends(app()->bound('tenant') ? 'layouts.tenant' : 'layouts.central')

@section('title', 'Plans')

@section('content')
@php
    $isTenant = app()->bound('tenant');

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
                        <span class="price heading-font text-4xl font-semibold text-slate-900" data-monthly="{{ $plan['price_monthly'] }}" data-annual="{{ $plan['price_annual'] }}">${{ $plan['price_monthly'] }}</span>
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
<script>
    const planSelectBaseUrl = @json($planSelectBaseUrl);
    const tenantCheckoutUrl = @json(Route::has('tenant.payments.checkout') ? route('tenant.payments.checkout') : null);
    const PROCEED_BASE = @json($proceedBase);
    const CENTRAL_BILLING_URL = @json($centralBillingFull);
    const IS_TENANT = @json($isTenant);
    const PLANS = @json(collect($plans)->keyBy('id'));
    let currentBillingMode = 'monthly';

    function selectPlan(plan) {
        const normalized = String(plan || '').toLowerCase();
        @if($isTenant)
            // Tenant context: show an intro modal before taking action. The
            // modal lets the tenant proceed to the manual checkout page or
            // submit a subscription request without navigating away.
            openPlanModal(normalized);
        @else
            window.location.href = planSelectBaseUrl + '?plan=' + encodeURIComponent(normalized);
        @endif
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

    function setBillingMode(mode) {
        const isAnnual = mode === 'annual';
        document.querySelectorAll('.price').forEach(function (node) {
            const monthly = Number(node.dataset.monthly || 0);
            const annual = Number(node.dataset.annual || monthly);
            node.textContent = '$' + (isAnnual ? annual : monthly);
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
                // Prefer PROCEED_BASE (absolute URL rendered server-side). If
                // we're in a tenant and PROCEED_BASE is not tenant-scoped,
                // fall back to constructing on current origin.
                let target = PROCEED_BASE || (location.origin + '/dashboard/payments/checkout');
                if (IS_TENANT && target.indexOf(location.origin) !== 0) {
                    target = location.origin + '/dashboard/payments/checkout';
                }
                proceed.setAttribute('href', target + qs);
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
        if (IS_TENANT) {
            // Force tenant-origin checkout to avoid server-generated
            // absolute URLs that point to central (e.g., http://localhost).
            window.location.href = location.origin + '/dashboard/payments/checkout' + qs;
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
            const res = await fetch('/subscription/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ plan: plan, billing_cycle: currentBillingMode })
            });

            if (!res.ok) {
                const text = await res.text();
                throw new Error('Request failed: ' + res.status + ' ' + text);
            }

            const payload = await res.json();
            if (payload && payload.success) {
                showToast(payload.message || 'Subscription requested. Pending approval.');
                if (typeof refreshBillingFromApi === 'function') refreshBillingFromApi();
                closePlanModal();
            } else {
                showToast((payload && payload.error) || 'Failed to request subscription');
            }
        } catch (err) {
            console.error(err);
            showToast('Unable to request subscription. ' + (err && err.message ? err.message : 'Please try again later.'));
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
                <button onclick="requestSubscriptionFromModal()" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Request Subscription</button>
                <a id="proceedToCheckout" href="/dashboard/payments/checkout" onclick="proceedToCheckoutFromModal(); return false;" class="w-full inline-block text-center rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-500">Proceed to Manual Checkout</a>
            </div>
            <p class="mt-3 text-xs text-slate-400">Your plan will activate after Central Admin approval.</p>
        </div>
    </div>
</div>
@endpush
