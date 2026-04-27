@extends('layouts.central')

@section('title', 'Update Requests')

@section('content')
<div class="p-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="heading-font text-2xl font-semibold mb-0">Update Requests</h1>
            <p class="text-sm text-slate-500">Tenant-initiated version update requests for review and action.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.update_requests.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Refresh</a>
        </div>
    </div>

    @php
        use Illuminate\Support\Str;
        use App\Models\UpdateRequest;

        // Summary counts (calculated in view for UI only — does not alter backend logic)
        $totalCount = UpdateRequest::count();
        $pendingCount = UpdateRequest::where('status', 'pending')->count();
        $approvedCount = UpdateRequest::where('status', 'approved')->count();
        $completedCount = UpdateRequest::where('status', 'completed')->count();
        $rejectedCount = UpdateRequest::where('status', 'rejected')->count();
    @endphp

    <!-- Compact summary cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        <div class="bg-white rounded-xl shadow-card p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-md bg-pink-50 flex items-center justify-center text-pink-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h18" stroke-width="1.5"/></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500">Total Requests</div>
                <div class="text-lg font-semibold">{{ $totalCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-md bg-yellow-50 flex items-center justify-center text-yellow-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 8v4l3 3" stroke-width="1.5"/></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500">Pending</div>
                <div class="text-lg font-semibold text-yellow-600">{{ $pendingCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-md bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6L9 17l-5-5" stroke-width="1.5"/></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500">Approved</div>
                <div class="text-lg font-semibold text-blue-600">{{ $approvedCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-md bg-green-50 flex items-center justify-center text-green-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6L9 17l-5-5" stroke-width="1.5"/></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500">Completed</div>
                <div class="text-lg font-semibold text-green-600">{{ $completedCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-md bg-red-50 flex items-center justify-center text-red-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" stroke-width="1.5"/></svg>
            </div>
            <div>
                <div class="text-xs text-slate-500">Rejected</div>
                <div class="text-lg font-semibold text-red-600">{{ $rejectedCount }}</div>
            </div>
        </div>
    </div>

    <!-- Filter bar (compact pill-style) -->
    <div class="mb-4">
        <form method="get" class="bg-white rounded-full shadow-sm px-3 py-2 flex items-center gap-3 flex-wrap">
            <div class="relative w-64">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 21l-4.35-4.35" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="11" cy="11" r="6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <input type="search" name="tenant_query" value="{{ request('tenant_query') ?: request('tenant_id') }}" placeholder="Search tenant or ID" class="input pl-10 w-full h-10 rounded-full" />
            </div>

            <div class="relative w-40">
                <select name="status" class="input w-full h-10 rounded-full">
                    <option value="">Any status</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>

            <div class="relative">
                <input id="dateRangeInput" readonly name="date_range" value="{{ request('date_range') }}" placeholder="Date range" class="input w-44 h-10 rounded-full cursor-pointer" />
                <div id="datePopover" class="hidden absolute mt-2 left-0 bg-white border rounded-lg shadow p-3 z-50 w-[320px]">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-medium text-slate-700">Date Range</div>
                        <button type="button" id="closeDatePopover" class="text-xs text-slate-400">Close</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <input type="date" id="dateFrom" class="input h-9" />
                        <input type="date" id="dateTo" class="input h-9" />
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" data-range="" class="date-preset text-xs px-2 py-1 rounded bg-slate-50">All</button>
                        <button type="button" data-range="7" class="date-preset text-xs px-2 py-1 rounded bg-slate-50">Last 7d</button>
                        <button type="button" data-range="30" class="date-preset text-xs px-2 py-1 rounded bg-slate-50">Last 30d</button>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" id="applyDates" class="text-sm px-3 py-1 rounded bg-pink-600 text-white">Apply</button>
                        <button type="button" id="clearDates" class="text-sm px-3 py-1 rounded border">Clear</button>
                    </div>
                </div>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('admin.update_requests.index') }}" id="clearFilters" class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-600">Clear</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-pink-600 to-pink-400">Filter</button>
            </div>
        </form>
    </div>

    <script>
        (function(){
            const dateInput = document.getElementById('dateRangeInput');
            const pop = document.getElementById('datePopover');
            const closeBtn = document.getElementById('closeDatePopover');
            const from = document.getElementById('dateFrom');
            const to = document.getElementById('dateTo');
            const apply = document.getElementById('applyDates');
            const clear = document.getElementById('clearDates');
            const presets = document.querySelectorAll('.date-preset');

            function showPopover(){ pop.classList.remove('hidden'); }
            function hidePopover(){ pop.classList.add('hidden'); }

            dateInput?.addEventListener('click', function(e){ e.stopPropagation(); showPopover(); });
            closeBtn?.addEventListener('click', hidePopover);
            document.addEventListener('click', function(e){ if(!pop.contains(e.target) && e.target !== dateInput) hidePopover(); });

            presets.forEach(btn => btn.addEventListener('click', function(){
                const days = this.getAttribute('data-range');
                if(!days){ from.value=''; to.value=''; return; }
                const t = new Date();
                const fromD = new Date(t.getFullYear(), t.getMonth(), t.getDate() - (parseInt(days) - 1));
                from.value = fromD.toISOString().slice(0,10);
                to.value = t.toISOString().slice(0,10);
            }));

            apply?.addEventListener('click', function(){
                if(from.value && to.value){ dateInput.value = from.value + ' - ' + to.value; }
                else if(!from.value && !to.value){ dateInput.value = ''; }
                hidePopover();
            });

            clear?.addEventListener('click', function(){ from.value = ''; to.value = ''; dateInput.value = ''; });

            // Clear filters link should reset query string when clicked
            document.getElementById('clearFilters')?.addEventListener('click', function(e){ e.preventDefault(); window.location = this.href; });
        })();
    </script>

    <!-- Table / Empty state -->
    <div class="bg-white rounded-2xl shadow-card p-3">
        @if($requests->count() === 0)
            <div class="flex flex-col items-center justify-center py-20">
                <svg class="w-16 h-16 text-slate-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h18M3 12h18M3 17h18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <div class="text-lg font-semibold text-slate-700">No update requests yet</div>
                <div class="text-sm text-slate-500 mb-4">There are currently no update requests from tenants.</div>
                <a href="{{ route('admin.update_requests.index') }}" class="btn btn-primary-gradient">Refresh</a>
            </div>
        @else
        <div class="hidden lg:block overflow-auto h-[360px] lg:h-[460px]">
            <table class="w-full table-auto text-sm">
                <thead class="bg-slate-50">
                <tr class="text-slate-500 text-xs uppercase tracking-wide">
                    <th class="p-3 text-left">Tenant</th>
                    <th class="p-3 text-left">Requested</th>
                    <th class="p-3 text-left">Current → Requested</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Notes</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach($requests as $r)
                <tr class="tenant-row transition duration-150 hover:bg-indigo-50/40">
                    <td class="max-w-[260px] px-3 py-2">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-semibold text-xs">{{ isset($tenants[$r->tenant_id]) ? strtoupper(substr($tenants[$r->tenant_id]->business_name,0,1)) : 'T' }}</div>
                            <div>
                                @if(isset($tenants[$r->tenant_id]))
                                    <a href="{{ route('tenants.show', $tenants[$r->tenant_id]->getTenantKey()) }}" class="font-medium text-slate-900 text-sm">{{ $tenants[$r->tenant_id]->business_name }}</a>
                                @else
                                    <div class="font-medium text-slate-900 text-sm">{{ $r->tenant_id }}</div>
                                @endif
                                <div class="text-xs text-slate-400">{{ $r->tenant_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="font-medium text-sm">{{ $r->requested_at->format('M d, Y h:i A') }}</div>
                        <div class="text-xs text-slate-500">{{ $r->requested_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="font-medium">{{ $r->current_version }} → {{ $r->requested_version }}</div>
                            @if(!empty($r->latest_version))
                                <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Available</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        @php
                            $status = $r->status;
                            $badgeClass = match($status) {
                                'pending' => 'bg-yellow-50 text-yellow-700',
                                'approved' => 'bg-blue-50 text-blue-700',
                                'completed' => 'bg-green-50 text-green-700',
                                'rejected' => 'bg-red-50 text-red-700',
                                default => 'bg-slate-50 text-slate-700'
                            };
                        @endphp
                        <span class="inline-flex items-center gap-2 text-xs px-2 py-1 rounded-full {{ $badgeClass }}">
                            <span class="w-2 h-2 rounded-full" style="background: currentColor; opacity: .9"></span>
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-700">{{ Str::limit($r->notes, 80) }}</td>
                    <td class="px-3 py-2 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('admin.update_requests.show', $r->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-1.5 text-sm font-semibold text-purple-600 bg-white">
                                <svg class="w-4 h-4 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <span>View Details</span>
                            </a>

                            <div class="relative">
                                <button type="button" onclick="toggleDropdown('dd-{{ $r->id }}')" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-100 bg-white text-slate-600"> 
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                                </button>
                                <div id="dd-{{ $r->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded shadow z-40">
                                    <form method="post" action="{{ route('admin.update_requests.update_status', $r->id) }}">@csrf<input type="hidden" name="status" value="approved" /><button type="button" onclick="confirmAction(this.form, 'Approve this request?')" class="w-full text-left px-3 py-2 text-sm">Approve</button></form>
                                    <form method="post" action="{{ route('admin.update_requests.update_status', $r->id) }}">@csrf<input type="hidden" name="status" value="rejected" /><button type="button" onclick="confirmAction(this.form, 'Reject this request?')" class="w-full text-left px-3 py-2 text-sm">Reject</button></form>
                                    <form method="post" action="{{ route('admin.update_requests.update_status', $r->id) }}">@csrf<input type="hidden" name="status" value="completed" /><button type="button" onclick="confirmAction(this.form, 'Mark this request as completed?')" class="w-full text-left px-3 py-2 text-sm">Mark Completed</button></form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
        
        <!-- Mobile stacked cards -->
        <div class="lg:hidden space-y-3">
            @foreach($requests as $r)
            <div class="bg-white rounded-xl shadow p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-semibold text-xs">{{ isset($tenants[$r->tenant_id]) ? strtoupper(substr($tenants[$r->tenant_id]->business_name,0,1)) : 'T' }}</div>
                        <div>
                            <div class="font-semibold text-sm">{{ $tenants[$r->tenant_id]->business_name ?? $r->tenant_id }}</div>
                            <div class="text-xs text-slate-400">{{ $r->requested_at->format('M d, Y h:i A') }} · {{ $r->requested_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="text-sm">
                        <div class="mb-1"><span class="text-sm font-medium">{{ $r->current_version }} → {{ $r->requested_version }}</span></div>
                        @php $s = $r->status; @endphp
                        <div><span class="text-xs px-2 py-1 rounded-full {{ $s=='pending' ? 'bg-yellow-50 text-yellow-700' : ($s=='approved' ? 'bg-blue-50 text-blue-700' : ($s=='completed' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700')) }}">{{ ucfirst($s) }}</span></div>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xs text-slate-600">{{ Str::limit($r->notes, 90) }}</div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.update_requests.show', $r->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-1.5 text-sm font-semibold text-purple-600 bg-white">
                            <svg class="w-4 h-4 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <span class="text-sm">View Details</span>
                        </a>
                        <button onclick="toggleDropdown('ddm-{{ $r->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-slate-100 bg-white text-slate-600"> 
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="mt-4 flex items-center justify-between">
        <div>{{ $requests->links() }}</div>
        <div class="text-sm text-slate-500">Showing {{ $requests->firstItem() ?: 0 }}–{{ $requests->lastItem() ?: 0 }} of {{ $requests->total() }}</div>
    </div>
</div>

@push('scripts')
<script>
function toggleDropdown(id) {
    document.querySelectorAll('[id^="dd-"]').forEach(el=>{ if(el.id!==id) el.classList.add('hidden'); });
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.toggle('hidden');
}

function confirmAction(form, message) {
    if(confirm(message)) {
        form.submit();
    }
}

function applyPerPage(select) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', select.value);
    window.location.search = params.toString();
}

// close dropdowns on outside click
document.addEventListener('click', function(e){
    if(!e.target.closest('[id^="dd-"]') && !e.target.closest('button')){
        document.querySelectorAll('[id^="dd-"]').forEach(el=>el.classList.add('hidden'));
    }
});

</script>
@endpush

@endsection
