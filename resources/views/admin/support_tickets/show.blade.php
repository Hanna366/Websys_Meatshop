@extends('layouts.central')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-2">Support Ticket #{{ $ticket->id }}</h1>

    <div class="mb-4">
        <strong>Tenant:</strong>
        @if($tenant)
            <a href="{{ route('tenants.show', $tenant->getTenantKey()) }}">{{ $tenant->business_name }}</a> ({{ $ticket->tenant_id }})
        @else
            {{ $ticket->tenant_id }}
        @endif
    </div>

    <div class="mb-4">
        <strong>Version:</strong> {{ $ticket->version }}<br />
        <strong>Last update:</strong> {{ $ticket->last_update_at ? $ticket->last_update_at->toDateTimeString() : 'N/A' }}
    </div>

    <div class="mb-4 bg-white p-4 rounded shadow">
        <pre class="whitespace-pre-wrap">{{ $ticket->message }}</pre>
    </div>

    <form method="post" action="{{ route('admin.support_tickets.update_status', $ticket->id) }}">
        @csrf
        <div class="flex items-center gap-2">
            <label class="font-medium">Status</label>
            <select name="status" class="input">
                <option value="open" {{ $ticket->status=='open' ? 'selected' : '' }}>Open</option>
                <option value="assigned" {{ $ticket->status=='assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="resolved" {{ $ticket->status=='resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ $ticket->status=='closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button class="btn btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection
