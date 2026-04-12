@extends('layouts.central')

@section('content')
@php
    $subscription = is_array($tenant->subscription) ? $tenant->subscription : [];
    $periodStart = $subscription['current_period_start'] ?? optional($tenant->plan_started_at)->toDateString();
    $periodEnd = $subscription['current_period_end'] ?? optional($tenant->plan_ends_at)->toDateString();
    $address = is_array($tenant->business_address) ? implode(', ', $tenant->business_address) : ($tenant->business_address ?? '');
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="heading-font mb-1 text-2xl font-semibold text-slate-900">Tenant Details</h2>
                <p class="mb-0 text-sm text-slate-500">Manage tenant profile, access lifecycle, and subscription periods.</p>
            </div>
            <a href="{{ route('tenants.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Back to list
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('tenant_password_emailed'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="font-semibold">Temporary admin password emailed</div>
                    <div class="mt-1">This tenant was provisioned with an auto-generated temporary password. For security, the temporary password is sent to the tenant admin only and is not displayed here.</div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center gap-2">
                        <button onclick="window.location.href='mailto:' + '{{ $tenant->admin_email }}'" class="inline-flex items-center rounded border border-slate-200 px-3 py-2 text-sm font-semibold">Email Admin</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="mb-0 list-disc ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card xl:col-span-2">
            <h3 class="heading-font mb-4 text-lg font-semibold text-slate-900">Tenant Profile</h3>

            <form method="POST" action="{{ route('tenants.update', $tenant->tenant_id) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Business Name</label>
                        <input type="text" name="business_name" value="{{ old('business_name', $tenant->business_name) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Business Email</label>
                        <input type="email" name="business_email" value="{{ old('business_email', $tenant->business_email) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Business Phone</label>
                        <input type="text" name="business_phone" value="{{ old('business_phone', $tenant->business_phone) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Domain</label>
                        <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}" placeholder="branch.localhost" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Admin Name</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name', $tenant->admin_name) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Admin Email</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email', $tenant->admin_email) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Business Address</label>
                    <textarea name="business_address" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">{{ old('business_address', $address) }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-600 hover:text-white">Save Profile</button>
                </div>
            </form>
        </section>

        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                <h3 class="heading-font mb-4 text-base font-semibold text-slate-900">Lifecycle Status</h3>
                <form method="POST" action="{{ route('tenants.updateStatus', $tenant->tenant_id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tenant Status</label>
                        <select name="status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="active" {{ ($tenant->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($tenant->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="disabled" {{ ($tenant->status ?? '') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                            <option value="unpaid" {{ ($tenant->status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Payment Status</label>
                        <select name="payment_status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="paid" {{ ($tenant->payment_status ?? 'paid') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ ($tenant->payment_status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="overdue" {{ ($tenant->payment_status ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Disabled Message</label>
                        <input type="text" name="disabled_message" value="{{ old('disabled_message', $tenant->disabled_message ?? $tenant->suspended_message ?? 'Please contact your administrator.') }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-600 hover:text-white">Update Lifecycle</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                <h3 class="heading-font mb-4 text-base font-semibold text-slate-900">Subscription</h3>
                <form method="POST" action="{{ route('tenants.updateSubscription', $tenant->tenant_id) }}" class="space-y-3">
                    @csrf
                    <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Plan</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @php
                                        $plans = array_keys(
                                            (array) config('plans.definitions', ['basic'=>[],'standard'=>[],'premium'=>[],'enterprise'=>[]])
                                        );
                                        $currentPlan = strtolower((string) ($tenant->plan ?? data_get($tenant->subscription, 'plan', 'basic')));
                                        $badgeMap = [
                                            'primary' => 'bg-indigo-600 text-white',
                                            'warning' => 'bg-amber-400 text-black',
                                            'danger' => 'bg-rose-600 text-white',
                                            'dark' => 'bg-slate-800 text-white',
                                        ];
                                    @endphp

                                    @foreach($plans as $planKey)
                                        @php
                                            $badge = \App\Services\SubscriptionService::getPlanBadgeColor($planKey);
                                            $badgeClass = $badgeMap[$badge] ?? 'bg-slate-100 text-slate-800';
                                            $isSelected = $currentPlan === strtolower($planKey);
                                        @endphp
                                        <label class="inline-flex items-center gap-3 rounded-xl border px-3 py-2 cursor-pointer transition {{ $isSelected ? 'ring-2 ring-indigo-300' : 'hover:shadow-sm' }}">
                                            <input type="radio" name="plan" value="{{ $planKey }}" class="hidden" {{ $isSelected ? 'checked' : '' }}>
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full {{ $badgeClass }} text-xs font-semibold">{{ ucfirst($planKey[0]) }}</span>
                                            <span class="text-sm font-medium text-slate-700">{{ ucfirst($planKey) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Billing Cycle</label>
                        <select name="billing_cycle" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="monthly" {{ ($subscription['billing_cycle'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="annual" {{ ($subscription['billing_cycle'] ?? '') === 'annual' ? 'selected' : '' }}>Annual</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Subscription Status</label>
                        <select name="subscription_status" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                            <option value="active" {{ ($subscription['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="unpaid" {{ ($subscription['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="expired" {{ ($subscription['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ ($subscription['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Current Period Start</label>
                            <input type="date" name="current_period_start" value="{{ old('current_period_start', $periodStart) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Current Period End</label>
                            <input type="date" name="current_period_end" value="{{ old('current_period_end', $periodEnd) }}" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                        </div>
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-600 hover:text-white">Update Subscription</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('copyGeneratedPassword')?.addEventListener('click', function () {
        const el = document.getElementById('generatedPassword');
        if (!el) return;
        const text = el.textContent || el.innerText || '';
        navigator.clipboard?.writeText(text).then(function () {
            alert('Copied temporary password to clipboard. Share it securely.');
        }).catch(function () {
            prompt('Copy the temporary password:', text);
        });
    });
</script>
@endpush
