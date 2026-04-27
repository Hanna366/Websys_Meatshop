@extends('layouts.tenant')

@section('content')
<div class="min-h-screen py-8">

    <div class="mx-auto max-w-6xl px-4">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}" class="text-slate-500 hover:text-slate-700 text-lg">&larr;</a>
                <div>
                    <h1 class="heading-font text-4xl font-extrabold tracking-tight">Configure your plan</h1>
                    <p class="text-sm checkout-label mt-1">Choose a payment method and submit proof for admin approval</p>
                </div>
            </div>
            @php
                $accentClasses = [
                    'emerald' => [
                        'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'ring' => 'from-emerald-500/20 to-emerald-100',
                        'button' => 'border-emerald-200 text-emerald-700 hover:bg-emerald-600 hover:text-white',
                        'check' => 'text-emerald-600',
                    ],
                    'indigo' => [
                        'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'ring' => 'from-indigo-500/20 to-indigo-100',
                        'button' => 'border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white',
                        'check' => 'text-indigo-600',
                    ],
                    'rose' => [
                        'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'ring' => 'from-rose-500/20 to-rose-100',
                        'button' => 'border-rose-200 text-rose-700 hover:bg-rose-600 hover:text-white',
                        'check' => 'text-rose-600',
                    ],
                ];
                $accent = $accentClasses['rose'];
            @endphp
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <main class="lg:col-span-2">
                <div class="rounded-2xl checkout-card p-6">
                    <h2 class="heading-font text-2xl font-semibold mb-4">Payment method</h2>

                    @if($errors->any())
                        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-rose-700">
                            <ul class="list-disc pl-5 text-sm">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.payments.store') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan_key }}">
                        <input type="hidden" name="billing_cycle" value="{{ $billing }}">

                        <div class="grid grid-cols-4 gap-4 mb-4">
                            @foreach(['GCash','Bank Transfer','Cash','Manual Payment'] as $method)
                                <label class="group relative block rounded-xl border border-slate-200 bg-white p-4 text-center cursor-pointer hover:shadow-md peer-checked:border-rose-500">
                                    <input type="radio" name="payment_method" value="{{ $method }}" class="peer sr-only" {{ old('payment_method') === $method || (!old('payment_method') && $loop->first) ? 'checked' : '' }}>
                                    <div class="text-sm font-medium text-slate-800">{{ $method }}</div>
                                    <div class="text-xs text-slate-400 mt-1">Select</div>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Reference number</label>
                            <input name="reference_number" value="{{ old('reference_number') }}" placeholder="Reference or transaction ID" class="w-full rounded-md bg-white border border-slate-200 p-3 text-sm text-slate-800 placeholder:text-slate-400">
                            @error('reference_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Proof of payment</label>
                            <div class="flex items-center gap-3">
                                <label class="rounded-md btn-primary-gradient px-4 py-2 text-sm cursor-pointer inline-flex items-center gap-2">
                                    <span class="text-white">Choose file</span>
                                    <input type="file" name="proof" accept="image/jpeg,image/png,application/pdf" class="sr-only">
                                </label>
                                <div class="text-sm text-slate-500">JPG, PNG or PDF — max 5MB</div>
                            </div>
                            @error('proof') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Message (optional)</label>
                            <textarea name="notes" rows="4" class="w-full rounded-md bg-white border border-slate-200 p-3 text-sm text-slate-800 placeholder:text-slate-400">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Primary submit moved to the aside sidebar; removed inline form submit here -->
                    </form>
                </div>
            </main>

            <aside>
                <article class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card {{ !empty($plan['popular']) ? 'ring-2 ring-rose-300/60' : '' }}">
                    @if(!empty($plan['popular']))
                        <div class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-rose-700 to-rose-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Most Popular</div>
                    @endif
                    <div class="mb-5">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $accent['badge'] }}">{{ ucfirst($plan_key) }}</span>
                        <h3 class="heading-font mt-3 text-2xl font-semibold text-slate-900">{{ ucfirst($plan_key) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ data_get($plan, 'description', 'Plan details') }}</p>
                    </div>

                    <div class="mb-5">
                        <div class="flex items-end gap-1">
                            <span class="price heading-font text-4xl font-semibold text-slate-900">₱{{ number_format((float) data_get($plan, 'price_monthly', 0), 0) }}</span>
                            <span class="mb-1 text-sm text-slate-500">/month</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Billed monthly. Annual saves about 15%.</p>
                        <div class="mt-4 h-1.5 rounded-full bg-gradient-to-r {{ $accent['ring'] }}"></div>
                    </div>

                    <ul class="space-y-2.5 text-sm mb-4 text-slate-700">
                        @foreach(data_get($plan, 'features', []) as $feature)
                            <li class="flex items-start gap-2.5 {{ data_get($feature, 'included', true) ? 'text-slate-700' : 'text-slate-400' }}">
                                @if(data_get($feature, 'included', true))
                                    <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0 {{ $accent['check'] }}"></i>
                                @else
                                    <i data-lucide="minus-circle" class="mt-0.5 h-4 w-4 shrink-0 text-slate-300"></i>
                                @endif
                                <span>{{ data_get($feature, 'label', is_string($feature) ? $feature : '') }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        <button type="button" onclick="document.querySelector('form').submit()" class="btn-primary-gradient w-full inline-flex items-center justify-center rounded-xl px-6 py-3 text-white font-semibold">Submit Payment for Review</button>
                    </div>
                </article>
            </aside>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">Scale</span>
                            <h3 class="mt-3 text-3xl font-extrabold mb-1">{{ ucfirst($plan_key) }}</h3>
                            <p class="text-sm text-slate-500 mb-3">For advanced operations and multi-branch optimization.</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-slate-400">Monthly</div>
                            <div class="text-3xl font-extrabold text-slate-900">₱{{ number_format((float) data_get($plan, 'price_monthly', 0), 2) }}</div>
                        </div>
                    </div>

                    <ul class="space-y-2 text-sm mb-4 text-slate-700 mt-4">
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-1">•</span><span>Advanced analytics dashboard</span></li>
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-1">•</span><span>Unlimited export (CSV/Excel/PDF)</span></li>
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-1">•</span><span>API access and batch operations</span></li>
                        <li class="flex items-start gap-2"><span class="text-rose-500 mt-1">•</span><span>Unlimited users and priority support</span></li>
                    </ul>

                    <div class="border-t border-slate-100 pt-4 text-sm">
                        <div class="flex justify-between text-slate-500"><span>Monthly subscription</span><span>₱{{ number_format((float) data_get($plan, 'price_monthly', 0), 2) }}</span></div>
                        <div class="flex justify-between text-slate-500"><span>Estimated tax</span><span>₱0.00</span></div>
                        <div class="mt-3 flex justify-between font-semibold text-slate-900"><span>Due today</span><span>₱{{ number_format((float) data_get($plan, 'price_monthly', 0), 2) }}</span></div>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">Your plan will activate after Central Admin approves your payment.</p>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
