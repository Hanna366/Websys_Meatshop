@extends('layouts.tenant')

@section('title', 'User Management')

@section('content')
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="heading-font text-3xl font-semibold mb-2">User Management</h1>
            <p class="text-sm text-slate-500">Manage users and their roles</p>
        </div>
        @if(auth()->user()->role !== 'cashier')
            <a href="/users/create" class="inline-flex items-center gap-2 rounded-xl border border-transparent bg-gradient-to-r from-rose-600 to-pink-500 px-4 py-2 text-sm font-semibold text-white shadow-md hover:opacity-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add User
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-white p-4 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('new_user_credentials'))
        @php $creds = session('new_user_credentials'); @endphp
        <div class="mb-4 rounded-xl border border-indigo-200 bg-white p-4">
            <h3 class="mb-2 font-semibold text-indigo-900">New User Credentials</h3>
            @if($creds['email_sent'] ?? false)
                <div class="mb-3 rounded-lg bg-white border border-emerald-200 p-3 text-emerald-700">
                    <p class="text-sm"><strong>Credentials have been sent to {{ $creds['email'] }}</strong></p>
                </div>
            @endif
            <div class="space-y-1 text-sm text-indigo-800">
                <p><strong>Name:</strong> {{ $creds['name'] }}</p>
                <p><strong>Email:</strong> {{ $creds['email'] }}</p>
                <p><strong>Username:</strong> {{ $creds['username'] }}</p>
                <p><strong>Password:</strong> {{ $creds['password'] }}</p>
                <p><strong>Role:</strong> {{ ucfirst($creds['role']) }}</p>
            </div>
            <p class="mt-2 text-xs text-indigo-600">Please copy these credentials - they won't be shown again!</p>
        </div>
    @endif

    @if(session('reset_credentials'))
        @php $creds = session('reset_credentials'); @endphp
        <div class="mb-4 rounded-xl border border-amber-200 bg-white p-4">
            <h3 class="mb-2 font-semibold text-amber-900">Password Reset - New Credentials</h3>
            @if($creds['email_sent'] ?? false)
                <div class="mb-3 rounded-lg bg-white border border-emerald-200 p-3 text-emerald-700">
                    <p class="text-sm"><strong>New password has been sent to {{ $creds['email'] }}</strong></p>
                </div>
            @endif
            <div class="space-y-1 text-sm text-amber-800">
                <p><strong>Name:</strong> {{ $creds['name'] }}</p>
                <p><strong>Username:</strong> {{ $creds['username'] }}</p>
                <p><strong>New Password:</strong> {{ $creds['password'] }}</p>
            </div>
            <p class="mt-2 text-xs text-amber-600">Please copy this password - it won't be shown again!</p>
        </div>
    @endif

    <div class="overflow-auto bg-white rounded-2xl shadow-card">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-4 text-left font-semibold text-slate-700">Name</th>
                    <th class="p-4 text-left font-semibold text-slate-700">Email</th>
                    <th class="p-4 text-left font-semibold text-slate-700">Username</th>
                    <th class="p-4 text-left font-semibold text-slate-700">Role</th>
                    <th class="p-4 text-left font-semibold text-slate-700">Status</th>
                    <th class="p-4 text-center font-semibold text-slate-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50">
                    <td class="p-4">
                        <div class="font-medium text-slate-900">{{ $user->name }}</div>
                    </td>
                    <td class="p-4 text-slate-600">{{ $user->email }}</td>
                    <td class="p-4 text-slate-600">{{ $user->username }}</td>
                    <td class="p-4">
                        @php
                            $roleColors = [
                                'admin' => 'bg-purple-100 text-purple-700',
                                'cashier' => 'bg-emerald-100 text-emerald-700',
                                'staff' => 'bg-slate-100 text-slate-700',
                            ];
                        @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="p-4">
                        @php
                            $statusColors = [
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'inactive' => 'bg-slate-100 text-slate-700',
                                'suspended' => 'bg-rose-100 text-rose-700',
                            ];
                        @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$user->status] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if(auth()->user()->role !== 'cashier' && auth()->user()->id !== $user->id)
                                @if(auth()->user()->role === 'owner' || (auth()->user()->role === 'manager' && $user->role !== 'owner'))
                                    <a href="/users/{{ $user->id }}/edit" class="rounded-lg border border-indigo-200 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-50">
                                        Edit
                                    </a>
                                    @if(auth()->user()->role === 'owner')
                                        <form action="/users/{{ $user->id }}/reset-password" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-amber-200 px-3 py-1 text-xs font-medium text-amber-700 hover:bg-amber-50" onclick="return confirm('Reset password for {{ $user->name }}?')">
                                                Reset Password
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
