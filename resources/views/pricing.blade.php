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
                    <div class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-rose-500 to-indigo-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Most Popular</div>
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

    function selectPlan(plan) {
        const normalized = String(plan || '').toLowerCase();
        @if($isTenant)
            // Tenant context: POST a subscription request so we don't redirect
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            // Use a relative tenant URL so the browser stays on the current host
            // and include credentials so session cookie + CSRF are sent.
            fetch('/subscription/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ plan: normalized })
            }).then(function (res) {
                if (!res.ok) {
                    return res.text().then(function (text) {
                        throw new Error('Request failed: ' + res.status + ' ' + text);
                    });
                }
                return res.json();
            }).then(function (payload) {
                if (payload && payload.success) {
                    showToast(payload.message || 'Subscription requested. Pending approval.');
                    if (typeof refreshBillingFromApi === 'function') {
                        refreshBillingFromApi();
                    }
                } else {
                    showToast((payload && payload.error) || 'Failed to request subscription');
                }
            }).catch(function (err) {
                console.error(err);
                showToast('Unable to request subscription. ' + (err && err.message ? err.message : 'Please try again later.'));
            });
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
    }

    document.getElementById('monthlyToggle')?.addEventListener('click', function () {
        setBillingMode('monthly');
    });

    document.getElementById('annualToggle')?.addEventListener('click', function () {
        setBillingMode('annual');
    });
</script>
@endpush
