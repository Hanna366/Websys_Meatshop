@extends('layouts.tenant')

@section('page_title', 'System Updates')
@section('page_subtitle', 'View app version status and request updates')

@section('content')
<div class="space-y-6">
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="package" class="h-4 w-4 text-indigo-600"></i>
                <h3 class="text-sm font-semibold text-slate-900">Current Version</h3>
            </div>
            <p class="text-2xl font-bold text-indigo-700">{{ $installedVersion }}</p>
            <p class="mt-1 text-xs text-slate-500">Installed on this tenant</p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="arrow-up-right" class="h-4 w-4 text-emerald-600"></i>
                <h3 class="text-sm font-semibold text-slate-900">Latest Available</h3>
            </div>
            <p class="text-2xl font-bold text-emerald-700">{{ $latestVersion }}</p>
            <p class="mt-1 text-xs text-slate-500">From central releases</p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="info" class="h-4 w-4 text-slate-600"></i>
                <h3 class="text-sm font-semibold text-slate-900">Status</h3>
            </div>
            <p class="text-2xl font-bold text-{{ $updateAvailable ? 'amber' : 'emerald' }}-700">{{ $updateAvailable ? 'Update Available' : 'Up to Date' }}</p>
            <p class="mt-1 text-xs text-slate-500">Last checked centrally</p>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/70 bg-white shadow-card p-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="heading-font text-lg font-semibold">Release Notes</h2>
            <div class="text-xs text-slate-500">Latest</div>
        </div>
        @if($latestRelease)
            <div class="prose max-w-none text-sm text-slate-700">{!! nl2br(e($latestRelease->description ?? ($latestRelease->release_notes ?? 'No release notes available.'))) !!}</div>
        @else
            <p class="text-sm text-slate-500">No release information available.</p>
        @endif

        <div class="mt-4 flex items-center gap-3">
            @if(config('updates.tenant_self_update', false) && $updateAvailable)
                <form method="POST" action="#" onsubmit="alert('Self-update is not implemented in this demo.'); return false;">
                    @csrf
                    <button class="rounded-xl border border-emerald-200 bg-emerald-100 px-3 py-2 text-sm font-medium text-emerald-700">Update Now</button>
                </form>
            @else
                @php
                    $requestAction = '/dashboard/updates/request' . (request()->query('tenant') ? '?tenant=' . request()->query('tenant') : '');
                @endphp
                <form method="POST" action="{{ $requestAction }}">
                    @csrf
                    <input type="hidden" name="target_version" value="{{ $latestVersion }}">
                    <button type="submit" class="rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700">Request Update</button>
                </form>
            @endif

            <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600" onclick="document.getElementById('reportModal').classList.remove('hidden')">Report Issue</button>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/70 bg-white shadow-card p-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="heading-font text-lg font-semibold">Your Update Requests</h2>
            <div class="text-xs text-slate-500">Recent</div>
        </div>
        @if(isset($myRequests) && $myRequests->isNotEmpty())
            <div class="space-y-2">
                @foreach($myRequests as $req)
                    <div class="rounded-lg border border-slate-100 p-3 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold">{{ $req->current_version ?? 'N/A' }} → {{ $req->requested_version ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">Status: {{ ucfirst($req->status) }} · Requested: {{ optional($req->requested_at)->format('M d, Y H:i') }}</div>
                        </div>
                        <div>
                            <a href="{{ route('tenant.updates.history') }}" class="text-xs text-indigo-600">View History</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-500">You have not requested any updates yet.</p>
        @endif
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="history" class="h-4 w-4 text-slate-600"></i>
                <h3 class="text-sm font-semibold text-slate-900">Last Update Activity</h3>
            </div>
            @if($lastLog)
                <p class="text-sm text-slate-700">Attempt: <strong>{{ $lastLog->from_version }} → {{ $lastLog->to_version }}</strong></p>
                <p class="text-sm text-slate-500">Status: {{ ucfirst($lastLog->status) }}</p>
                <p class="text-sm text-slate-500">At: {{ optional($lastLog->created_at)->format('M d, Y H:i') }}</p>
            @else
                <p class="text-sm text-slate-500">No update activity recorded for this tenant.</p>
            @endif
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="headphones" class="h-4 w-4 text-slate-600"></i>
                <h3 class="text-sm font-semibold text-slate-900">Having issues?</h3>
            </div>
            <p class="text-sm text-slate-500">If you encounter an issue after updating, report it and support will follow up.</p>
            <div class="mt-3">
                <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600" onclick="document.getElementById('reportModal').classList.remove('hidden')">Report Issue</button>
            </div>
        </div>
    </section>
</div>

<!-- Report modal -->
<div id="reportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="w-full max-w-lg rounded-xl bg-white p-6">
        <h3 class="text-lg font-semibold">Report an Issue</h3>
        @php
            $reportAction = '/dashboard/updates/report' . (request()->query('tenant') ? '?tenant=' . request()->query('tenant') : '');
        @endphp
        <form method="POST" action="{{ $reportAction }}" class="mt-4">
            @csrf
            <div class="mb-3">
                <label class="block text-sm text-slate-700">Describe the issue</label>
                <textarea name="message" rows="5" class="w-full rounded border border-slate-200 p-2 text-sm" required></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition" onclick="document.getElementById('reportModal').classList.add('hidden')">Cancel</button>
                <button type="submit" class="btn-primary-gradient rounded-lg px-5 py-2 text-sm font-semibold text-white">Submit</button>
            </div>
        </form>
    </div>
</div>

@endsection
