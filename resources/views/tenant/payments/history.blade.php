@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="rounded-2xl bg-white p-6 shadow-card">
        <h2 class="text-xl font-semibold mb-4">Payment History</h2>

        @if(session('status'))
            <div class="rounded p-3 bg-emerald-50 text-emerald-700 mb-4">{{ session('status') }}</div>
        @endif

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-600">
                    <th>Submitted</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Admin Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                    <tr class="border-t">
                        <td>{{ $p->created_at->toDateString() }}</td>
                        <td>{{ ucfirst($p->plan_id) }}</td>
                        <td>{{ number_format($p->amount,2) }}</td>
                        <td>{{ $p->payment_method }}</td>
                        <td>
                            @if($p->status === 'pending')<span class="px-2 py-1 rounded bg-amber-100 text-amber-800">Pending</span>
                            @elseif($p->status === 'approved')<span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800">Approved</span>
                            @else<span class="px-2 py-1 rounded bg-rose-100 text-rose-800">Rejected</span>@endif
                        </td>
                        <td>{{ $p->admin_notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
