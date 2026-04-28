@extends('layouts.tenant')

@section('title', 'Profile - Meat Shop POS')
@section('page_title', 'Profile')
@section('page_subtitle', 'Manage account details, security preferences, and subscription status')

@section('header_actions')
    <button type="button" onclick="editProfile()" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        <i data-lucide="pencil" class="h-4 w-4"></i>
        Edit Profile
    </button>
@endsection

@php
    $sessionRole = strtolower((string) session('user.role', 'owner'));
    $profileRoleLabel = match ($sessionRole) {
        'owner', 'administrator' => 'Administrator',
        'inventory_staff', 'inventory staff', 'inventory', 'staff', 'manager' => 'Cashier',
        'cashier' => 'Cashier',
        default => ucfirst($sessionRole),
    };
@endphp

@section('content')
<section class="grid gap-6 lg:grid-cols-12">
    <aside class="space-y-6 lg:col-span-4">
        <section class="rounded-3xl border border-white/70 bg-white/90 p-6 text-center shadow-card">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user.name', 'Owner User')) }}&background=1e40af&color=fff" alt="Profile Avatar" class="mx-auto mb-4 h-24 w-24 rounded-full border-4 border-white shadow">
            <h2 class="heading-font text-xl font-semibold text-slate-900">{{ session('user.name', 'Owner User') }}</h2>
            <p class="text-sm text-slate-500">{{ session('user.email', 'owner@meatshop.com') }}</p>
            <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ session('user.plan') == 'Premium' ? 'bg-rose-100 text-rose-700' : (session('user.plan') == 'Standard' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700') }}">
                <i data-lucide="crown" class="h-3.5 w-3.5"></i>
                {{ session('user.plan', 'Basic') }} Plan
            </span>
        </section>

        <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card">
            <h3 class="mb-4 text-base font-semibold text-slate-900">Account Stats</h3>
            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl bg-slate-50 p-3"><p class="text-2xl font-bold text-slate-900">247</p><p class="text-xs text-slate-500">Sales</p></div>
                <div class="rounded-2xl bg-slate-50 p-3"><p class="text-2xl font-bold text-slate-900">156</p><p class="text-xs text-slate-500">Products</p></div>
                <div class="rounded-2xl bg-slate-50 p-3"><p class="text-2xl font-bold text-slate-900">89</p><p class="text-xs text-slate-500">Customers</p></div>
                <div class="rounded-2xl bg-slate-50 p-3"><p class="text-2xl font-bold text-slate-900">12</p><p class="text-xs text-slate-500">Suppliers</p></div>
            </div>
        </section>
    </aside>

    <div class="space-y-6 lg:col-span-8">
        <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h3 class="heading-font mb-4 text-lg font-semibold text-slate-900">Profile Information</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Full Name</p><p class="mt-1 font-medium text-slate-800">{{ session('user.name', 'Owner User') }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Email</p><p class="mt-1 font-medium text-slate-800">{{ session('user.email', 'owner@meatshop.com') }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Phone</p><p class="mt-1 font-medium text-slate-800">+63 912 345 6789</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Role</p><p class="mt-1 font-medium text-slate-800">{{ $profileRoleLabel }}</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Department</p><p class="mt-1 font-medium text-slate-800">Management</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Location</p><p class="mt-1 font-medium text-slate-800">Manila, Philippines</p></div>
            </div>
        </section>

        <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h3 class="heading-font mb-4 text-lg font-semibold text-slate-900">Subscription</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Current Plan</p><p class="mt-1 font-medium text-slate-800">Premium - PHP 8,300/month</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Next Billing Date</p><p class="mt-1 font-medium text-slate-800">March 20, 2026</p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Account Status</p><p class="mt-1"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span></p></div>
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Member Since</p><p class="mt-1 font-medium text-slate-800">January 15, 2026</p></div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @php
                    $authCtx = (string) session('auth_context', 'central');
                    $isTenantCtx = preg_match('/^tenant:(.+)$/', $authCtx, $m);
                    $tenantHost = $isTenantCtx ? $m[1] : null;
                    if ($tenantHost) {
                        $scheme = request()->getScheme();
                        $port = request()->getPort();
                        $portPart = ($port && $port !== 80 && $port !== 443) ? ':'.$port : '';
                        $tenantUrl = $scheme.'://'.$tenantHost.$portPart.'/pricing';
                    }
                @endphp
                <a href="{{ $tenantHost ? $tenantUrl : url('/pricing') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Upgrade Plan</a>
                <button type="button" onclick="viewBilling()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Billing History</button>
            </div>
        </section>

        <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h3 class="heading-font mb-4 text-lg font-semibold text-slate-900">Security</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div><p class="text-xs uppercase tracking-wide text-slate-500">Last Login</p><p class="mt-1 font-medium text-slate-800">{{ now()->format('F j, Y g:i A') }}</p></div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Two-Factor Authentication</p>
                    <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" id="twoFactor" class="rounded border-slate-300"> Enable 2FA</label>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" onclick="changePassword()" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">Change Password</button>
                <button type="button" onclick="viewLoginHistory()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Login History</button>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
function toast(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2300, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

function editProfile() { toast('Profile editor is coming next.', 'info'); }
function changePassword() { toast('Password change flow is coming next.', 'info'); }
function viewBilling() { toast('Billing history feature coming soon.', 'info'); }
function viewLoginHistory() { toast('Login history feature coming soon.', 'info'); }
</script>
@endpush