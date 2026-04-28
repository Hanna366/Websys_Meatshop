@extends('layouts.tenant')

@section('title', 'Add New User')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="heading-font text-3xl font-semibold mb-2">Add New User</h1>
        <p class="text-sm text-slate-500">Create a new user with specific role and permissions</p>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl shadow-card p-6">
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/users" method="POST">
            @csrf

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                    placeholder="Enter full name">
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-slate-700">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                    placeholder="Enter email address">
                <p class="mt-1 text-xs text-slate-500">Username will be generated from the email automatically.</p>
            </div>

            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-slate-700">Role</label>
                <select name="role" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <div class="mt-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                    <p><strong>Cashier:</strong> Can access sales and view inventory only. Cannot add stock or manage other features.</p>
                    <p><strong>Admin:</strong> Full access to all features and settings.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-transparent bg-gradient-to-r from-rose-600 to-pink-500 px-6 py-3 text-sm font-semibold text-white shadow-md hover:opacity-95">
                    Create User
                </button>
                <a href="/users" class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Cancel
                </a>
            </div>

            <div class="mt-4 rounded-lg border border-amber-200 bg-white p-3 text-xs text-amber-700">
                <p><strong>Note:</strong> A random password will be generated automatically. The credentials will be displayed on the next page after successful creation.</p>
            </div>
        </form>
    </div>
</div>
@endsection
