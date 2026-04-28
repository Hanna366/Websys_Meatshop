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
                            <td class="px-4 py-3">{{ $req->created_at ? $req->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
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

                                    <div class="inline-flex items-center gap-2" id="actions-{{ $req->id }}">
                                        <button onclick="approveRequest({{ $req->id }})" class="btn-primary-gradient inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold w-28 text-center">Approve</button>
                                        <button onclick="rejectRequest({{ $req->id }})" class="btn-danger-gradient inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold w-28 text-center">Reject</button>
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

@push('scripts')
<script>
    (function () {
        const tbody = document.querySelector('tbody.divide-y');
        if (!tbody) return;

        // Determine last seen id from the first row rendered (requests ordered desc)
        let lastId = 0;
        try {
            const firstRowId = document.querySelector('tbody tr td')?.textContent || null;
            if (firstRowId) lastId = parseInt(firstRowId, 10) || 0;
        } catch (e) { lastId = 0; }

        async function pollUpdates() {
            try {
                const updatesPath = @json(url('/admin/subscription-requests/updates'));
                const url = new URL(updatesPath, location.origin);
                url.searchParams.set('since_id', lastId);
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) return;
                const json = await res.json();
                if (!json || !json.data || !json.data.length) return;
                json.data.forEach(function (r) {
                    // Build a table row matching existing markup
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-4 py-3">${r.id}</td>
                        <td class="px-4 py-3">${(r.tenant_id) ? r.tenant_id : ''}</td>
                        <td class="px-4 py-3">${(r.requested_plan||'').charAt(0).toUpperCase() + (r.requested_plan||'').slice(1)}</td>
                        <td class="px-4 py-3">${r.amount}</td>
                        <td class="px-4 py-3">${r.payment_reference || ''}</td>
                        <td class="px-4 py-3">${r.created_at}</td>
                        <td class="px-4 py-3">${(r.status||'').charAt(0).toUpperCase() + (r.status||'').slice(1)}</td>
                        <td class="px-4 py-3">
                            <div class="inline-flex items-center gap-2">
                                <form method="POST" action="/admin/subscription-requests/${r.id}/approve">@csrf
                                    <button class="btn-primary-gradient inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold w-28 text-center">Approve</button>
                                </form>
                                <form method="POST" action="/admin/subscription-requests/${r.id}/reject">@csrf
                                    <button class="btn-danger-gradient inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold w-28 text-center">Reject</button>
                                </form>
                            </div>
                        </td>
                    `;

                    // Prepend so newest appear at top
                    tbody.insertBefore(tr, tbody.firstChild);
                    lastId = Math.max(lastId, Number(r.id));
                });
            } catch (e) {
                console.warn('Polling subscription updates failed', e);
            }
        }

        // Poll every 1 second for new requests via AJAX
        setInterval(pollUpdates, 1000);
        pollUpdates();
        
        // Instant approve/reject via AJAX (no page reload)
        async function approveRequest(id) {
            try {
                const res = await fetch('/admin/subscription-requests/' + id + '/approve', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('actions-' + id).innerHTML = '<span class="text-emerald-600 font-semibold">Approved</span>';
                    const statusCell = document.querySelector('tr:has(#actions-' + id + ') > td:nth-child(7)');
                    if (statusCell) statusCell.textContent = 'Approved';
                } else {
                    alert(data.message || 'Approval failed');
                }
            } catch (e) {
                console.error(e);
                alert('Approval error');
            }
        }

        async function rejectRequest(id) {
            try {
                const res = await fetch('/admin/subscription-requests/' + id + '/reject', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('actions-' + id).innerHTML = '<span class="text-rose-600 font-semibold">Rejected</span>';
                    const statusCell = document.querySelector('tr:has(#actions-' + id + ') > td:nth-child(7)');
                    if (statusCell) statusCell.textContent = 'Rejected';
                } else {
                    alert(data.message || 'Rejection failed');
                }
            } catch (e) {
                console.error(e);
                alert('Rejection error');
            }
        }

        // Make functions globally accessible for onclick handlers
        window.approveRequest = approveRequest;
        window.rejectRequest = rejectRequest;
    })();
</script>
@endpush
