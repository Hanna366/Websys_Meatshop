@extends('layouts.tenant')

@section('page_title', 'Support Tickets')
@section('page_subtitle', 'Your reported issues and support requests')

@section('content')
<div class="space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <h2 class="heading-font text-lg font-semibold">Report an Issue</h2>
        <form method="POST" action="{{ route('tenant.support.store') }}" class="mt-3">
            @csrf
            <div class="mb-3">
                <label class="block text-sm text-slate-700">Describe the issue</label>
                <textarea name="message" rows="4" class="w-full rounded border border-slate-200 p-2 text-sm" required></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary-gradient rounded-lg px-5 py-2 text-sm font-semibold text-white">Submit</button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <h2 class="heading-font text-lg font-semibold mb-4">Your Tickets</h2>
        @if($tickets->isEmpty())
            <p class="text-sm text-slate-500">You have not reported any issues yet.</p>
        @else
            <div class="space-y-3">
                @foreach($tickets as $t)
                    <div class="rounded-lg border border-slate-100 p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold">{{ Str::limit($t->message, 80) }}</div>
                                <div class="text-xs text-slate-500">Version: {{ $t->version ?? '—' }}</div>
                            </div>
                            <div class="text-xs text-slate-400">{{ optional($t->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
