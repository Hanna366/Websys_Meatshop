@extends('layouts.central')

@section('title', 'Support Tickets')
@section('content')
<div class="p-6">
    <h1 class="heading-font text-3xl font-semibold mb-2">Tenant Support Tickets</h1>
    <p class="mb-6 text-sm text-slate-500">Review tenant support requests and respond or escalate as needed.</p>
    @php use Illuminate\Support\Str; @endphp

    <div class="mb-4 flex items-center justify-between">
        <form method="get" class="flex gap-2 items-center">
            <input type="text" name="tenant_id" value="{{ request('tenant_id') }}" placeholder="Tenant ID" class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100" />
            <!-- Custom dropdown to allow themed hover styling -->
            <div class="relative" x-data="{}">
                <input type="hidden" name="status" id="statusInput" value="{{ request('status') }}">
                <button type="button" id="statusToggle" aria-haspopup="listbox" aria-expanded="false" class="h-10 w-44 text-left rounded-xl border border-rose-300 bg-white px-4 pr-10 text-sm text-slate-700 outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-50 inline-flex items-center justify-between">
                    <span id="statusLabel">{{ ucfirst(request('status') ?: 'Any status') }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <ul id="statusMenu" role="listbox" tabindex="-1" class="hidden absolute mt-2 w-44 rounded-lg border border-rose-200 bg-white shadow-lg z-50 overflow-hidden">
                    @php $statuses = ['' => 'Any status', 'open' => 'Open', 'assigned' => 'Assigned', 'resolved' => 'Resolved', 'closed' => 'Closed']; @endphp
                    @foreach($statuses as $val => $label)
                        <li data-value="{{ $val }}" role="option" aria-selected="{{ request('status') === $val ? 'true' : 'false' }}" class="cursor-pointer px-4 py-2 text-sm text-slate-700 hover:bg-gradient-to-r hover:from-rose-500 hover:to-pink-400 hover:text-white {{ request('status') === $val ? 'bg-rose-100' : '' }}">
                            {{ $label }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <button class="h-10 inline-flex items-center gap-2 rounded-xl border border-transparent bg-gradient-to-tr from-rose-500 to-pink-400 px-4 py-2 text-sm font-semibold text-white shadow-md hover:opacity-95">Filter</button>
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
                    <td class="px-5 py-4">{{ $ticket->current_version }}</td>
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
@push('scripts')
<script>
    (function () {
        const toggle = document.getElementById('statusToggle');
        const menu = document.getElementById('statusMenu');
        const input = document.getElementById('statusInput');
        const label = document.getElementById('statusLabel');

        if (!toggle || !menu || !input) return;

        function openMenu() {
            menu.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            menu.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', function (e) {
            if (menu.classList.contains('hidden')) openMenu(); else closeMenu();
        });

        // click outside
        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) closeMenu();
        });

        menu.querySelectorAll('li[data-value]').forEach(function (li) {
            li.addEventListener('click', function () {
                const val = this.getAttribute('data-value');
                input.value = val;
                label.textContent = this.textContent.trim();
                // Update aria-selected
                menu.querySelectorAll('[role="option"]').forEach(el => el.setAttribute('aria-selected', 'false'));
                this.setAttribute('aria-selected', 'true');
                closeMenu();
            });
        });

        // keyboard support
        toggle.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault(); openMenu(); menu.querySelector('[role="option"]').focus();
            }
        });

        menu.querySelectorAll('[role="option"]').forEach(function (opt) {
            opt.setAttribute('tabindex', '-1');
            opt.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { closeMenu(); toggle.focus(); }
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
            });
        });
    })();
</script>
@endpush

    <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
