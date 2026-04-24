@extends('layouts.central')

@section('title', 'Support Tickets')
@section('content')
<div class="p-6">
    <h1 class="heading-font text-3xl font-semibold mb-2">Tenant Support Tickets</h1>
    <p class="mb-6 text-sm text-slate-500">Review tenant support requests and respond or escalate as needed.</p>
    @php use Illuminate\Support\Str; @endphp

    <div class="mb-4 flex items-center justify-between">
        <form method="get" class="flex gap-2">
            <input type="text" name="tenant_id" value="{{ request('tenant_id') }}" placeholder="Tenant ID" class="input" />
            <select name="status" class="input">
                <option value="">Any status</option>
                <option value="open" {{ request('status')=='open' ? 'selected' : '' }}>Open</option>
                <option value="assigned" {{ request('status')=='assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="resolved" {{ request('status')=='resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status')=='closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button class="btn btn-primary-gradient">Filter</button>
        </form>

        <div>
            <a href="{{ route('admin.support_tickets.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Refresh</a>
        </div>
    </div>

    <div class="overflow-auto bg-white rounded-2xl shadow-card p-4">
        <div class="overflow-auto h-[360px] lg:h-[460px]">
            <table class="min-w-[1100px] table-auto text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="p-2 text-left">Tenant</th>
                    <th class="p-2 text-left">ID</th>
                    <th class="p-2 text-left">Version</th>
                    <th class="p-2 text-left">Message</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Created</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach($tickets as $ticket)
                <tr class="tenant-row transition duration-150 hover:bg-indigo-50/40">
                    <td class="max-w-[220px] px-5 py-4 font-medium text-slate-900">
                        @if(isset($tenants[$ticket->tenant_id]))
                            <a href="{{ route('tenants.show', $tenants[$ticket->tenant_id]->getTenantKey()) }}" class="font-medium text-slate-900">{{ $tenants[$ticket->tenant_id]->business_name }}</a>
                            <div class="text-xs text-slate-500">{{ $ticket->tenant_id }}</div>
                        @else
                            <div class="text-sm text-slate-700">{{ $ticket->tenant_id }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-4">{{ $ticket->id }}</td>
                    <td class="px-5 py-4">{{ $ticket->version }}</td>
                    <td class="px-5 py-4">{{ Str::limit($ticket->message, 120) }}</td>
                    <td class="px-5 py-4">{{ ucfirst($ticket->status) }}</td>
                    <td class="px-5 py-4">{{ $ticket->created_at->toDateTimeString() }}</td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('admin.support_tickets.show', $ticket->id) }}" class="inline-flex items-center gap-1 rounded-xl border border-indigo-200 px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
