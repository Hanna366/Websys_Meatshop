@extends('layouts.tenant')

@section('title', 'Edit User')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="heading-font text-3xl font-semibold mb-2">Edit User</h1>
        <p class="text-sm text-slate-500">Update user information and role</p>
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

        <form action="/users/{{ $user->id }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                    placeholder="Enter full name">
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-slate-700">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100"
                    placeholder="Enter email address">
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-slate-700">Username</label>
                <input type="text" value="{{ $user->username }}" disabled
                    class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500">
                <p class="mt-1 text-xs text-slate-500">Username cannot be changed.</p>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-slate-700">Role</label>
                <select name="role" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-transparent bg-gradient-to-r from-rose-600 to-pink-500 px-6 py-3 text-sm font-semibold text-white shadow-md hover:opacity-95">
                    Update User
                </button>
                <a href="/users" class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
