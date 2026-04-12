@extends('layouts.central')

@section('title', 'Billing')

@section('content')
@php
    $centralBillingMode = (bool) ($centralBillingMode ?? false);
    $plan = $subscription['plan'] ?? 'basic';
    $price = $subscription['price'] ?? 0;
    $status = $subscription['status'] ?? 'inactive';
    $nextBilling = isset($subscription['next_billing_at']) && $subscription['next_billing_at'] instanceof \Carbon\Carbon
        ? $subscription['next_billing_at']->format('M d, Y')
        : 'N/A';
@endphp

<div class="space-y-6">
    <section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-1 text-2xl font-semibold text-slate-900">Billing</h2>
                <p class="mb-0 text-sm text-slate-500">
                    {{ $centralBillingMode ? 'Monitor tenant payment status and billing overview.' : 'Manage subscription invoices and payment history.' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($centralBillingMode)
                    @php $adminRequestsRoute = Route::has('admin.subscription_requests.index') ? route('admin.subscription_requests.index') : url('/admin/subscription-requests'); @endphp
                    <a href="{{ $adminRequestsRoute }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                        <i data-lucide="badge-dollar-sign" class="h-4 w-4"></i>
                        Pending Payments
                        @if(!empty($pending_count) && $pending_count > 0)
                            <span class="ml-2 inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ $pending_count }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                        <i data-lucide="badge-dollar-sign" class="h-4 w-4"></i>
                        View Plans
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">{{ $centralBillingMode ? 'Managed Tenants' : 'Current Plan' }}</p>
                    <h3 class="heading-font text-2xl font-semibold text-slate-900">{{ $centralBillingMode ? number_format((int) $plan) : ucfirst((string) $plan) }}</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                    <i data-lucide="package" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">{{ $centralBillingMode ? 'Estimated MRR' : 'Monthly Cost' }}</p>
                    <h3 class="heading-font text-2xl font-semibold text-slate-900">${{ number_format((float) $price, 2) }}</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                    <i data-lucide="wallet" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">Billing Status</p>
                    @php
                        $statusLower = strtolower((string) $status);
                        $statusClasses = [
                            'active' => 'bg-emerald-50 text-emerald-700',
                            'healthy' => 'bg-emerald-50 text-emerald-700',
                            'mixed' => 'bg-amber-50 text-amber-700',
                            'paid' => 'bg-emerald-50 text-emerald-700',
                            'pending' => 'bg-amber-50 text-amber-700',
                            'overdue' => 'bg-rose-50 text-rose-700',
                            'unpaid' => 'bg-rose-50 text-rose-700',
                            'failed' => 'bg-rose-50 text-rose-700',
                            'inactive' => 'bg-slate-100 text-slate-700',
                            'cancelled' => 'bg-slate-100 text-slate-700',
                        ][$statusLower] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ ucfirst((string) $status) }}</span>
                    @if ($centralBillingMode)
                        <p class="mt-2 text-xs text-slate-500">Paid: {{ (int) ($subscription['paid_tenants'] ?? 0) }} | Unpaid/Overdue: {{ (int) ($subscription['unpaid_tenants'] ?? 0) }}</p>
                    @endif
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <i data-lucide="shield-check" class="h-5 w-5"></i>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-slate-500">Next Billing Date</p>
                    <h3 class="heading-font text-2xl font-semibold text-slate-900">{{ $nextBilling }}</h3>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                    <i data-lucide="calendar-days" class="h-5 w-5"></i>
                </span>
            </div>
        </article>
    </section>

    <div id="pendingRequestBanner" class="px-6 py-4">
        @if (!empty($pending_request))
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 mb-4 text-amber-800">
                <strong>Pending payment:</strong>
                {{ $pending_request['requested_plan'] ?? '-' }} —
                Ref: {{ $pending_request['payment_reference'] ?? '-' }} —
                ${{ number_format((float) ($pending_request['amount'] ?? 0), 2) }}
            </div>
        @endif
    </div>

    

    <section class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-card">
        <div class="flex flex-col gap-3 border-b border-slate-200/70 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <h3 class="heading-font mb-0 text-lg font-semibold text-slate-900">Billing History</h3>
            <div class="relative">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                <input id="invoiceSearch" type="text" class="h-10 w-80 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" placeholder="{{ $centralBillingMode ? 'Search tenant, invoice, or status' : 'Search invoice, status, or plan' }}">
            </div>
        </div>

        @if (empty($billingHistory))
            <div class="px-6 py-12 text-center">
                <div class="mx-auto mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <i data-lucide="receipt-text" class="h-6 w-6"></i>
                </div>
                <h4 class="heading-font mb-1 text-lg font-semibold text-slate-900">{{ $centralBillingMode ? 'No tenant billing records yet' : 'No invoices yet' }}</h4>
                <p class="mb-0 text-sm text-slate-500">{{ $centralBillingMode ? 'Tenant payment records will appear here as tenants are updated.' : 'Invoices will appear here once your first billing cycle is processed.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" id="billingTable">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Invoice ID</th>
                            @if ($centralBillingMode)
                                <th class="px-5 py-3.5 font-semibold">Tenant</th>
                            @endif
                            <th class="px-5 py-3.5 font-semibold">Date</th>
                            <th class="px-5 py-3.5 font-semibold">Amount</th>
                            <th class="px-5 py-3.5 font-semibold">Plan</th>
                            <th class="px-5 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold">Payment Method</th>
                            <th class="px-5 py-3.5 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700" id="billingTableBody">
                        @foreach ($billingHistory as $entry)
                            @php
                                $invoiceStatus = strtolower((string) ($entry['status'] ?? 'pending'));
                                $invoiceStatusClass = [
                                    'paid' => 'bg-emerald-50 text-emerald-700',
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'unpaid' => 'bg-rose-50 text-rose-700',
                                    'overdue' => 'bg-rose-50 text-rose-700',
                                    'failed' => 'bg-rose-50 text-rose-700',
                                ][$invoiceStatus] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <tr class="billing-row transition duration-150 hover:bg-indigo-50/40">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $entry['id'] ?? '-' }}</td>
                                @if ($centralBillingMode)
                                    <td class="px-5 py-4 text-slate-700">{{ $entry['tenant_name'] ?? '-' }}</td>
                                @endif
                                <td class="px-5 py-4 text-slate-600">{{ $entry['date'] ?? '-' }}</td>
                                <td class="px-5 py-4 font-medium">${{ number_format((float) ($entry['amount'] ?? 0), 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $entry['plan'] ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $invoiceStatusClass }}">{{ ucfirst((string) ($entry['status'] ?? 'Unknown')) }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $entry['payment_method'] ?? '-' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" class="inline-flex items-center gap-1 rounded-xl border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-600 hover:text-white">
                                        View Invoice
                                        <i data-lucide="file-text" class="h-3.5 w-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('invoiceSearch')?.addEventListener('input', function (event) {
        const query = event.target.value.trim().toLowerCase();
        document.querySelectorAll('#billingTable .billing-row').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    async function refreshBillingFromApi() {
        try {
            const billingDataUrl = {{ $centralBillingMode ? "'" . route('subscription.billing.data') . "'" : "'/subscription/billing/data'" }};
            const response = await fetch(billingDataUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            if (!payload || !payload.success || !payload.data || !Array.isArray(payload.data.history)) {
                return;
            }
            // Show pending subscription request (if present)
            const pending = payload.data.pending_request;
            const pendingEl = document.getElementById('pendingRequestBanner');
            if (pendingEl) {
                if (pending) {
                    pendingEl.innerHTML = `
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 mb-4 text-amber-800">
                            <strong>Pending payment:</strong>
                            ${pending.requested_plan ?? '-'} —
                            Ref: ${pending.payment_reference ?? '-'} —
                            $${Number(pending.amount ?? 0).toFixed(2)}
                        </div>
                    `;
                } else {
                    pendingEl.innerHTML = '';
                }
            }

            const body = document.getElementById('billingTableBody');
            if (!body) {
                return;
            }

            body.innerHTML = payload.data.history.map(function (entry) {
                const status = String(entry.status || 'pending').toLowerCase();
                const statusClass = status === 'paid'
                    ? 'bg-emerald-50 text-emerald-700'
                    : (status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700');

                return `
                    <tr class="billing-row transition duration-150 hover:bg-indigo-50/40">
                        <td class="px-5 py-4 font-semibold text-slate-900">${entry.id ?? '-'}</td>
                        <td class="px-5 py-4 text-slate-600">${entry.date ?? '-'}</td>
                        <td class="px-5 py-4 font-medium">$${Number(entry.amount ?? 0).toFixed(2)}</td>
                        <td class="px-5 py-4"><span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">${entry.plan ?? '-'}</span></td>
                        <td class="px-5 py-4"><span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${statusClass}">${String(entry.status ?? 'Unknown')}</span></td>
                        <td class="px-5 py-4 text-slate-600">${entry.payment_method ?? '-'}</td>
                        <td class="px-5 py-4 text-right">
                            <button type="button" class="inline-flex items-center gap-1 rounded-xl border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-600 hover:text-white">View Invoice</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            // Keep server-rendered data when network refresh fails.
        }
    }

    @if (!$centralBillingMode)
        refreshBillingFromApi();
    @endif
</script>
@endpush
