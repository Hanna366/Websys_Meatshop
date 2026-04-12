@extends('layouts.central')

@section('title', 'Subscription Requests')

@section('header_actions')
    <a href="{{ route('subscription.billing') }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-700">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Back to Billing
    </a>
@endsection

@section('content')
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="heading-font mb-0 text-3xl font-semibold">Pending Subscription Requests</h2>
                <p class="text-sm text-slate-500">Review tenant subscription change requests and approve or reject them.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-4">{{ session('error') }}</div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Requested Plan</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Payment Ref</th>
                        <th class="px-4 py-3">Requested At</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($requests as $req)
                        <tr>
                            <td class="px-4 py-3">{{ $req->id }}</td>
                            <td class="px-4 py-3">{{ optional(App\Models\Tenant::where('tenant_id', $req->tenant_id)->first())->business_name ?? $req->tenant_id }}</td>
                            <td class="px-4 py-3">{{ ucfirst($req->requested_plan) }}</td>
                            <td class="px-4 py-3">{{ number_format($req->amount,2) }}</td>
                            <td class="px-4 py-3">{{ $req->payment_reference }}</td>
                            <td class="px-4 py-3">{{ $req->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">{{ ucfirst($req->status) }}</td>
                            <td class="px-4 py-3">
                                @if($req->status === 'pending')
                                    @php
                                        if (Route::has('admin.subscription_requests.approve')) {
                                            $approveRoute = route('admin.subscription_requests.approve', $req->id);
                                        } elseif (Route::has('admin.admin.subscription_requests.approve')) {
                                            $approveRoute = route('admin.admin.subscription_requests.approve', $req->id);
                                        } else {
                                            $approveRoute = url('/admin/subscription-requests/' . $req->id . '/approve');
                                        }

                                        if (Route::has('admin.subscription_requests.reject')) {
                                            $rejectRoute = route('admin.subscription_requests.reject', $req->id);
                                        } elseif (Route::has('admin.admin.subscription_requests.reject')) {
                                            $rejectRoute = route('admin.admin.subscription_requests.reject', $req->id);
                                        } else {
                                            $rejectRoute = url('/admin/subscription-requests/' . $req->id . '/reject');
                                        }
                                    @endphp

                                    <div class="inline-flex items-center gap-2">
                                        <form method="POST" action="{{ $approveRoute }}">
                                            @csrf
                                            <button class="btn-primary-gradient inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold w-28 text-center">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ $rejectRoute }}">
                                            @csrf
                                            <button class="btn-danger-gradient inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold w-28 text-center">Reject</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
