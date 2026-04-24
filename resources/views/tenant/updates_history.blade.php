@extends('layouts.tenant')

@section('page_title', 'Update History')
@section('page_subtitle', 'Recent update attempts for your tenant')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-4">
    <h2 class="heading-font text-lg font-semibold mb-4">Update History</h2>

    @if($updateLogs->isEmpty())
        <p class="text-sm text-slate-500">No update history for this tenant.</p>
    @else
        <div class="space-y-3">
            @foreach($updateLogs as $log)
                <div class="rounded-lg border border-slate-100 p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold">{{ $log->from_version }} → {{ $log->to_version }}</div>
                            <div class="text-xs text-slate-500">Status: {{ ucfirst($log->status) }}</div>
                        </div>
                        <div class="text-xs text-slate-400">{{ optional($log->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                    @if($log->error_message)
                        <div class="mt-2 text-sm text-rose-600">Error: {{ $log->error_message }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $updateLogs->links() }}</div>
    @endif
</div>
@endsection
