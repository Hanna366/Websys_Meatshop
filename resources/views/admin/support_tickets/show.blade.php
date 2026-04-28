@extends('layouts.central')

@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="heading-font text-3xl font-semibold mb-2">Support Ticket #{{ $ticket->id }}</h1>

    <div class="mb-6 text-slate-700">
        <div class="mb-1 text-sm text-slate-500">Tenant</div>
        <div class="font-medium text-slate-900">
            @if($tenant)
                <a href="{{ route('tenants.show', $tenant->getTenantKey()) }}" class="text-indigo-700 hover:underline">{{ $tenant->business_name }}</a>
                <div class="text-xs text-slate-500">{{ $ticket->tenant_id }}</div>
            @else
                <div class="text-sm text-slate-700">{{ $ticket->tenant_id }}</div>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-6 text-slate-700">
        <div>
            <div class="text-sm text-slate-500">Version</div>
            <div class="font-medium text-slate-900">{{ $ticket->current_version ?? $ticket->version ?? 'N/A' }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-500">Last update</div>
            <div class="font-medium text-slate-900">{{ $ticket->last_update_at ? $ticket->last_update_at->toDateTimeString() : 'N/A' }}</div>
        </div>
    </div>

    <div class="mb-6 bg-white rounded-2xl shadow-card p-6">
        <div class="whitespace-pre-wrap text-slate-800 text-sm">{{ $ticket->message }}</div>
    </div>

    <form method="post" action="{{ route('admin.support_tickets.update_status', $ticket->id) }}">
        @csrf
        <div class="flex items-center gap-3">
            <label class="font-medium text-slate-700">Status</label>
            <select name="status" class="h-10 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
                <option value="open" {{ $ticket->status=='open' ? 'selected' : '' }}>Open</option>
                <option value="assigned" {{ $ticket->status=='assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="resolved" {{ $ticket->status=='resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ $ticket->status=='closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button class="inline-flex items-center gap-2 rounded-xl border border-transparent bg-gradient-to-tr from-rose-500 to-pink-400 px-4 py-2 text-sm font-semibold text-white shadow-md hover:opacity-95">Update</button>
        </div>
    </form>
</div>
@endsection
