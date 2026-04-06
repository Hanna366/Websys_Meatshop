@extends('layouts.tenant')

@section('title', 'Settings - Meat Shop POS')
@section('page_title', 'Settings')
@section('page_subtitle', 'Configure branch preferences, users, backups, and operations defaults')

@section('header_actions')
    <button type="button" onclick="notify('Settings reset to last saved values.', 'info')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
        Reset
    </button>
    <button type="button" onclick="notify('Settings saved successfully.', 'success')" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
        <i data-lucide="save" class="h-4 w-4"></i>
        Save Changes
    </button>
@endsection

@section('content')
@php
    $userStoreAction = \Illuminate\Support\Facades\Route::has('tenant.users.store')
        ? route('tenant.users.store')
        : url('/settings/users');
@endphp

@if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if($errors->has('user_create'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        {{ $errors->first('user_create') }}
    </div>
@endif

<section class="grid gap-6 max-w-7xl mx-auto lg:grid-cols-4">
    <aside class="lg:col-span-1">
        <div class="sticky top-6 rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Sections</p>
            <nav class="space-y-1 text-sm">
                <a href="#general" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">General</a>
                <a href="#business" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Business</a>
                <a href="#tax" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Tax and Currency</a>
                <a href="#inventory" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Inventory</a>
                <a href="#users" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Users</a>
                <a href="#backup" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Backup</a>
            </nav>
        </div>
    </aside>

    <div class="lg:col-span-3 space-y-6">
        <section id="general" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">General Settings</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="Meat Shop POS" placeholder="Shop Name">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="admin@meatshop.com" placeholder="Shop Email">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="+63 912 3456" placeholder="Phone">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="123 Market Street, Manila" placeholder="Address">
            </div>
        </section>

        <section id="business" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Business Information</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="Premium Meat Shop Inc.">
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Retail</option><option>Wholesale</option><option>Both</option></select>
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="123-456-789-000">
                <input class="rounded-xl border border-slate-200 px-3 py-2.5" value="BL-2024-12345">
            </div>
            <textarea class="mt-4 w-full rounded-xl border border-slate-200 px-3 py-2.5" rows="3">Premium quality meat products serving the community since 2010.</textarea>
        </section>

        <section id="tax" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Tax and Currency</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Philippine Peso (PHP)</option><option>USD</option><option>EUR</option></select>
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Before (PHP 100.00)</option><option>After (100.00 PHP)</option></select>
                <input type="number" class="rounded-xl border border-slate-200 px-3 py-2.5" value="12" step="0.1" placeholder="VAT Rate">
                <input type="number" class="rounded-xl border border-slate-200 px-3 py-2.5" value="0" step="0.1" placeholder="Service Charge">
            </div>
        </section>

        <section id="inventory" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Inventory Controls</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <input type="number" class="rounded-xl border border-slate-200 px-3 py-2.5" value="20" min="1" max="100" placeholder="Low Stock Alert %">
                <select class="rounded-xl border border-slate-200 px-3 py-2.5"><option selected>Email Notification</option><option>SMS Notification</option><option>Both</option><option>None</option></select>
            </div>
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                <label class="flex items-center gap-2"><input type="checkbox" checked class="rounded border-slate-300"> Enable automatic reorder suggestions</label>
                <label class="flex items-center gap-2"><input type="checkbox" checked class="rounded border-slate-300"> Track product expiry dates</label>
                <label class="flex items-center gap-2"><input type="checkbox" class="rounded border-slate-300"> Enable batch tracking</label>
            </div>
        </section>

        <section id="users" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="heading-font text-lg font-semibold text-slate-900">User Management</h2>
            </div>

            <form method="POST" action="{{ $userStoreAction }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div class="sm:col-span-1 lg:col-span-1">
                    <label for="add_user_name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Full Name</label>
                    <input id="add_user_name" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Juan Dela Cruz" required>
                    @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-1 lg:col-span-1">
                    <label for="add_user_email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                    <input id="add_user_email" type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="cashier@shop.com" required>
                    @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-1 lg:col-span-1">
                    <label for="add_user_username" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Username (Optional)</label>
                    <input id="add_user_username" name="username" value="{{ old('username') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="auto-generated if empty">
                    @error('username')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-1 lg:col-span-1">
                    <label for="add_user_role" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Role</label>
                    <select id="add_user_role" name="role" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" required>
                        @foreach(($availableRoles ?? ['Administrator', 'Cashier']) as $roleOption)
                            <option value="{{ $roleOption }}" {{ old('role', 'Cashier') === $roleOption ? 'selected' : '' }}>{{ $roleOption }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-1 lg:col-span-1">
                    <label for="add_user_password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                    <input id="add_user_password" type="password" name="password" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" minlength="8" data-role-conditional="cashier" data-optional-for="cashier">
                    <p id="password_hint" class="mt-1 text-xs text-slate-500 hidden">Password will be auto-generated and sent via email (optional to provide your own)</p>
                    @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-1 lg:col-span-1">
                    <label for="add_user_password_confirmation" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm Password</label>
                    <input id="add_user_password_confirmation" type="password" name="password_confirmation" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" minlength="8" data-role-conditional="cashier" data-optional-for="cashier">
                    @error('password_confirmation')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex justify-end mt-2">
                    <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">Add User</button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 font-medium">Username</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse(($tenantUsers ?? []) as $tenantUser)
                            @php
                                $roleLabel = ($tenantRoleTablesReady ?? false) ? optional($tenantUser->roles->first())->name : null;
                                if (!$roleLabel) {
                                    $roleLabel = strtolower((string) $tenantUser->role) === 'owner' ? 'Administrator' : ucfirst((string) $tenantUser->role);
                                }
                                $normalizedRole = strtolower(str_replace(['_', '-'], ' ', (string) $roleLabel));
                                if (in_array($normalizedRole, ['inventory staff', 'inventory', 'staff', 'manager'], true)) {
                                    $roleLabel = 'Cashier';
                                }
                                $roleClasses = strtolower($roleLabel) === 'administrator'
                                    ? 'bg-rose-100 text-rose-700'
                                    : 'bg-sky-100 text-sky-700';

                                $status = strtolower((string) ($tenantUser->status ?? 'active'));
                                $statusClasses = $status === 'active'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-200 text-slate-700';
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $tenantUser->username }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $tenantUser->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $roleClasses }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClasses }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if(in_array(strtolower((string) $roleLabel), ['owner', 'administrator'], true))
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium text-slate-500 bg-slate-100">Protected</span>
                                    @else
                                        <div class="flex flex-col gap-2 items-end">
                                            <button onclick="toggleEditForm('edit-{{ $tenantUser->id }}')" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                                Edit
                                            </button>
                                            
                                            <div id="edit-{{ $tenantUser->id }}" class="hidden w-full mt-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                                @php
                                                    $userUpdateAction = \Illuminate\Support\Facades\Route::has('tenant.users.update')
                                                        ? route('tenant.users.update', $tenantUser->id)
                                                        : url('/settings/users/' . $tenantUser->id);
                                                @endphp
                                                <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('tenant.users.update') ? route('tenant.users.update', $tenantUser->id) : url('/settings/users/' . $tenantUser->id) }}" class="space-y-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="grid grid-cols-1 gap-2">
                                                        <input type="text" name="name" value="{{ $tenantUser->name }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs" placeholder="Name" required>
                                                        <input type="text" name="username" value="{{ $tenantUser->username }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs" placeholder="Username" required>
                                                        <input type="email" name="email" value="{{ $tenantUser->email }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs" placeholder="Email" required>
                                                        <select name="role" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                                                            @foreach(($availableRoles ?? ['Administrator', 'Cashier']) as $roleOption)
                                                                <option value="{{ $roleOption }}" {{ strtolower($roleLabel) === strtolower($roleOption) ? 'selected' : '' }}>{{ $roleOption }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                    </div>
                                                    <div class="flex gap-2 justify-end">
                                                        <button type="button" onclick="toggleEditForm('edit-{{ $tenantUser->id }}')" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800 transition-colors">
                                                            Save
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            @php
                                                $userStatusAction = \Illuminate\Support\Facades\Route::has('tenant.users.status')
                                                    ? route('tenant.users.status', $tenantUser->id)
                                                    : url('/settings/users/' . $tenantUser->id . '/status');
                                            @endphp
                                            <form method="POST" action="{{ $userStatusAction }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors {{ $status === 'active' ? 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                    {{ $status === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span class="text-sm font-medium">No users found for this tenant yet.</span>
                                        <span class="text-xs text-slate-400">Create your first user account to get started</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="backup" class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
            <h2 class="heading-font mb-4 text-lg font-semibold text-slate-900">Backup and Restore</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4"><p class="mb-1 text-sm font-semibold text-slate-800">Manual Backup</p><p class="mb-3 text-xs text-slate-500">Create and download a backup snapshot.</p><button type="button" onclick="notify('Backup download started.', 'success')" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Download Backup</button></div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="mb-1 text-sm font-semibold text-slate-800">Restore</p><p class="mb-3 text-xs text-slate-500">Upload an SQL or JSON backup file.</p><input type="file" class="mb-3 block w-full text-sm"><button type="button" onclick="notify('Restore initiated.', 'warning')" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">Restore Backup</button></div>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
function notify(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2300, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

function toggleEditForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('hidden');
    }
}

// Handle conditional password fields based on role selection
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('add_user_role');
    const passwordField = document.getElementById('add_user_password');
    const passwordConfirmField = document.getElementById('add_user_password_confirmation');
    const passwordHint = document.getElementById('password_hint');
    
    function updatePasswordFields() {
        // For all user accounts, password is optional (auto-generated)
        // Remove required attribute for all roles
        passwordField.removeAttribute('required');
        passwordConfirmField.removeAttribute('required');
        
        // Show hint for all roles
        passwordHint.classList.remove('hidden');
        
        // Add visual indication that field is optional
        passwordField.style.backgroundColor = '#f8fafc';
        passwordField.style.borderColor = '#cbd5e1';
        passwordConfirmField.style.backgroundColor = '#f8fafc';
        passwordConfirmField.style.borderColor = '#cbd5e1';
        
        // Update hint text based on role
        const selectedRole = roleSelect.value.toLowerCase();
        if (selectedRole === 'cashier') {
            passwordHint.textContent = 'Password will be auto-generated and sent via email for cashier accounts';
        } else if (selectedRole === 'manager' || selectedRole === 'owner') {
            passwordHint.textContent = 'Password will be auto-generated and sent via email (optional to provide your own)';
        } else {
            passwordHint.textContent = 'Password will be auto-generated and sent via email (optional to provide your own)';
        }
    }
    
    // Initial check
    updatePasswordFields();
    
    // Update on role change
    roleSelect.addEventListener('change', updatePasswordFields);
    
    // Form validation override
    const form = roleSelect.closest('form');
    form.addEventListener('submit', function(e) {
        const selectedRole = roleSelect.value.toLowerCase();
        
        // For all accounts, if password is not provided, clear fields before submission
        if (!passwordField.value.trim()) {
            passwordField.value = '';
            passwordConfirmField.value = '';
        }
    });
});
</script>
@endpush