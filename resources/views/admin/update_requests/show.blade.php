@extends('layouts.central')

@section('title', 'Update Request')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="heading-font mb-1 text-2xl font-semibold text-slate-900">Update Request #{{ $request->id }}</h1>
                <p class="mb-0 text-sm text-slate-500">Tenant update request details and admin actions.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.update_requests.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Back to Requests
                </a>

                @php
                    $status = $request->status;
                    $pill = match($status) {
                        'pending' => 'bg-yellow-50 text-yellow-800',
                        'approved' => 'bg-indigo-50 text-indigo-700',
                        'completed' => 'bg-emerald-50 text-emerald-700',
                        'rejected' => 'bg-red-50 text-red-700',
                        default => 'bg-slate-50 text-slate-700'
                    };
                @endphp

                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium {{ $pill }}">
                    <span class="w-2 h-2 rounded-full" style="background: currentColor; opacity: .9"></span>
                    {{ ucfirst($status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card p-6 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="flex items-start gap-4 lg:col-span-2">
                <div class="w-14 h-14 rounded-lg bg-pink-50 flex items-center justify-center text-pink-600">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"></path></svg>
                </div>
                <div>
                    <div class="text-sm text-slate-500">Tenant</div>
                    <div class="font-semibold text-lg">
                        @if($tenant)
                            <a href="{{ route('tenants.show', $tenant->getTenantKey()) }}" class="text-slate-900">{{ $tenant->business_name }}</a>
                        @else
                            <span class="text-slate-900">{{ $request->tenant_id }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                        <span class="font-mono text-xs">{{ $request->tenant_id }}</span>
                        <button type="button" onclick="copyToClipboard('{{ $request->tenant_id }}')" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="9" y="9" width="10" height="10" rx="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">Current Version</div>
                        <div class="mt-1"><span class="inline-block px-3 py-1 rounded-md bg-indigo-50 text-indigo-700 text-sm">{{ $request->current_version }}</span></div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">Requested Version</div>
                        <div class="mt-1"><span class="inline-block px-3 py-1 rounded-md bg-rose-50 text-rose-700 text-sm">{{ $request->requested_version }}</span></div>
                    </div>
                </div>

                <div class="mt-2 text-sm text-slate-600 flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4"></path><path d="M8 2v4"></path></svg>
                    <div>
                        <div>{{ $request->requested_at ? $request->requested_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        <div class="text-xs text-slate-400">{{ $request->requested_at ? $request->requested_at->diffForHumans() : '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-slate-50 border border-slate-100 rounded-md p-4">
            <div class="text-sm text-slate-500 mb-2">Request Notes</div>
            <div class="text-sm text-slate-700">{{ $request->notes ?? '—' }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-700">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7h18"></path></svg>
                </div>
                <div>
                    <div class="font-semibold">Update Request Status</div>
                    <div class="text-xs text-slate-400">Manage status and add admin notes</div>
                </div>
            </div>
        </div>

        <form method="post" id="statusForm" action="{{ route('admin.update_requests.update_status', $request->id) }}">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                <div class="lg:col-span-1">
                    <div class="text-sm text-slate-500 mb-2">Status</div>
                    <select name="status" id="statusSelect" class="input w-full h-10 rounded-md">
                        <option value="pending" {{ $request->status=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $request->status=='approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $request->status=='rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="completed" {{ $request->status=='completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-sm text-slate-500 mb-2">Admin Notes (optional)</div>
                    <textarea id="adminNotes" name="notes" maxlength="250" class="input w-full h-20 rounded-md p-3" placeholder="Add a note about this request..."></textarea>
                    <div class="text-xs text-slate-400 text-right mt-1" id="notesCount">0 / 250</div>
                </div>

            <div class="mt-4 border-t pt-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="setStatusAndSubmit('rejected')" class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm border border-red-200 text-red-600 bg-white">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Reject
                        </button>
                        <button type="button" onclick="setStatusAndSubmit('approved')" class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm border border-indigo-200 text-indigo-700 bg-white">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 6L9 17l-5-5"></path></svg>
                            Approve
                        </button>
                        <button type="button" onclick="setStatusAndSubmit('completed')" class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm border border-emerald-200 text-emerald-700 bg-white">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 6L9 17l-5-5"></path></svg>
                            Mark Completed
                        </button>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary-gradient rounded-md px-4 py-2 text-sm font-semibold">Update Status</button>
                    </div>
                </div>
            </div>
            </div>
        </form>
    </div>
</div>

<script>
    function copyToClipboard(text){
        navigator.clipboard?.writeText(text).then(()=>{ alert('Tenant ID copied'); }).catch(()=>{});
    }

    function setStatusAndSubmit(status){
        const sel = document.getElementById('statusSelect');
        sel.value = status;
        document.getElementById('statusForm').submit();
    }

    // notes counter
    const notes = document.getElementById('adminNotes');
    const count = document.getElementById('notesCount');
    if(notes){
        notes.addEventListener('input', function(){ count.textContent = this.value.length + ' / 250'; });
    }
</script>
@endsection
