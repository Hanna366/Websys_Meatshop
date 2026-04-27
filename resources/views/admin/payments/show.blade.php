@extends('layouts.admin')

@section('content')
<div class="rounded-2xl bg-white p-6 shadow-card">
    <h2 class="text-xl font-semibold">Payment #{{ $payment->id }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="md:col-span-2">
            <p><strong>Tenant:</strong> {{ $payment->tenant->business_name ?? $payment->tenant_id }}</p>
            <p><strong>Plan:</strong> {{ ucfirst($payment->plan_id) }}</p>
            <p><strong>Amount:</strong> {{ number_format($payment->amount,2) }}</p>
            <p><strong>Method:</strong> {{ $payment->payment_method }}</p>
            <p><strong>Reference:</strong> {{ $payment->reference_number }}</p>
            <p class="mt-3"><strong>Notes:</strong><br>{{ $payment->notes }}</p>

            <div class="mt-4">
                @if($payment->proof_path)
                    <h4 class="font-semibold">Proof</h4>
                    <div class="mt-2">
                        @if(Str::endsWith($payment->proof_path, ['.pdf']))
                            <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank">View PDF</a>
                        @else
                            <img src="{{ asset('storage/' . $payment->proof_path) }}" style="max-width:400px" alt="proof">
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <aside>
            <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Admin Notes</label>
                    <textarea name="admin_notes" class="w-full rounded border p-2" rows="4">{{ old('admin_notes', $payment->admin_notes) }}</textarea>
                </div>
                <div class="flex gap-2 mt-3">
                    <button type="submit" class="bg-emerald-600 text-white px-3 py-2 rounded">Approve</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}" class="mt-3">
                @csrf
                <div>
                    <button class="bg-rose-600 text-white px-3 py-2 rounded">Reject</button>
                </div>
            </form>
        </aside>
    </div>
</div>
@endsection
