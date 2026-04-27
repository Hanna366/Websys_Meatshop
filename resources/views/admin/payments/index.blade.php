@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-white p-6 shadow-card">
        <h2 class="text-xl font-semibold">Payments Review</h2>
        <div class="mt-4">
            <form method="GET" class="flex gap-2">
                <select name="status" class="rounded px-2 py-1 border">
                    <option value="">All</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Pending</option>
                    <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Approved</option>
                    <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Rejected</option>
                </select>
                <button class="rounded bg-rose-600 text-white px-3 py-1">Filter</button>
            </form>
        </div>

        <table class="w-full text-sm mt-4">
            <thead>
                <tr class="text-left text-slate-600">
                    <th>Submitted</th>
                    <th>Tenant</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                    <tr class="border-t">
                        <td>{{ $p->created_at->toDateString() }}</td>
                        <td>{{ $p->tenant->business_name ?? $p->tenant_id }}</td>
                        <td>{{ ucfirst($p->plan_id) }}</td>
                        <td>{{ number_format($p->amount,2) }}</td>
                        <td>{{ $p->payment_method }}</td>
                        <td>{{ ucfirst($p->status) }}</td>
                        <td><a href="{{ route('admin.payments.show', $p->id) }}" class="text-rose-600">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $payments->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
