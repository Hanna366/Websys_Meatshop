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
                            <div class="mt-3 rounded-md bg-amber-50 border border-amber-200 p-2 text-amber-800 text-sm inline-block">This is a simulated checkout — no real charges will be made. Submit proof and admins will approve manually.</div>
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
            @if(session('status'))
                <div class="lg:col-span-3">
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">{{ session('status') }}</div>
                </div>
            @endif
                    <main class="lg:col-span-2">
                                <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-card">
                                    <h2 class="heading-font text-2xl font-semibold mb-4 text-slate-900">Payment method</h2>

                    @if($errors->any())
                        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-rose-700">
                            <ul class="list-disc pl-5 text-sm">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/dashboard/payments/checkout') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan_key }}">
                        <input type="hidden" name="billing_cycle" value="{{ $billing }}">

                        <div class="grid grid-cols-4 gap-4 mb-4">
                            @foreach(['GCash','Bank Transfer','Cash','Manual Payment'] as $method)
                                <label class="group relative block rounded-2xl border border-slate-200 bg-white p-4 text-center cursor-pointer hover:shadow-card peer-checked:border-rose-500">
                                    <input type="radio" name="payment_method" value="{{ $method }}" class="peer sr-only" {{ old('payment_method') === $method || (!old('payment_method') && $loop->first) ? 'checked' : '' }}>
                                    <div class="text-sm font-medium text-slate-900">{{ $method }}</div>
                                    <div class="text-xs text-slate-500 mt-1">Select</div>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Reference number</label>
                            <input name="reference_number" value="{{ old('reference_number') }}" placeholder="Reference or transaction ID" class="w-full rounded-xl bg-white border border-slate-200 p-3 text-sm text-slate-900 placeholder:text-slate-400">
                            @error('reference_number') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Proof of payment</label>
                            <div class="flex items-center gap-3">
                                <label class="rounded-lg bg-rose-600 px-4 py-2 text-sm cursor-pointer inline-flex items-center gap-2">
                                    <span class="text-white">Choose file</span>
                                    <input type="file" name="proof" accept="image/jpeg,image/png,application/pdf" class="sr-only">
                                </label>
                                <div class="text-sm text-slate-400">JPG, PNG or PDF — max 5MB</div>
                            </div>
                            @error('proof') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Message (optional)</label>
                            <textarea name="notes" rows="4" class="w-full rounded-xl bg-white border border-slate-200 p-3 text-sm text-slate-900 placeholder:text-slate-400">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Card input preview to match desired appearance -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Card details</label>
                            <div class="space-y-3">
                                <input name="card_number" placeholder="Card number" class="w-full rounded-2xl bg-white border border-slate-200 p-4 text-sm text-slate-900 placeholder:text-slate-400">
                                <div class="grid grid-cols-2 gap-3">
                                    <input name="card_expiry" placeholder="MM / YY" class="rounded-2xl bg-white border border-slate-200 p-3 text-sm text-slate-900 placeholder:text-slate-400">
                                    <input name="card_cvc" placeholder="CVC" class="rounded-2xl bg-white border border-slate-200 p-3 text-sm text-slate-900 placeholder:text-slate-400">
                                </div>
                                <p class="text-xs text-slate-400">Card details are optional in this simulated checkout (no real card processing).</p>
                            </div>
                        </div>

                        <!-- Primary submit moved to the aside sidebar; removed inline form submit here -->
                    </form>
                </div>
            </main>

            <aside>
                <article class="relative rounded-3xl border border-white/70 bg-white/90 p-6 shadow-card text-slate-900">
                    @if(!empty($plan['popular']))
                        <div class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-rose-700 to-rose-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Most Popular</div>
                    @endif
                    <div class="mb-5">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $accent['badge'] }}">{{ ucfirst($plan_key) }}</span>
                        <h3 class="heading-font mt-3 text-2xl font-semibold text-white">{{ ucfirst($plan_key) }}</h3>
                        <p class="mt-1 text-sm text-slate-400">{{ data_get($plan, 'description', 'Plan details') }}</p>
                    </div>

                    <div class="mb-5">
                        <div class="flex items-end gap-1">
                            <span class="price heading-font text-3xl font-semibold">₱{{ number_format((float) data_get($plan, 'price_monthly', 0), 2) }}</span>
                            <span class="mb-1 text-sm text-slate-500">/month</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Billed monthly. Estimated tax shown at checkout.</p>
                        <div class="mt-4 h-1 rounded-full bg-slate-100"></div>
                    </div>

                    <ul class="space-y-2.5 text-sm mb-4 text-slate-300">
                        @foreach(data_get($plan, 'features', []) as $featureKey => $feature)
                            @php
                                if (is_string($feature)) {
                                    $label = $feature;
                                    $included = true;
                                } elseif (is_bool($feature)) {
                                    $label = ucwords(str_replace('_', ' ', $featureKey));
                                    $included = $feature;
                                } else {
                                    $label = data_get($feature, 'label', ucwords(str_replace('_', ' ', $featureKey)));
                                    $included = data_get($feature, 'included', true);
                                }
                            @endphp
                            <li class="flex items-start gap-2.5 {{ $included ? 'text-slate-700' : 'text-slate-400' }}">
                                @if($included)
                                    <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0 {{ $accent['check'] }}"></i>
                                @else
                                    <i data-lucide="minus-circle" class="mt-0.5 h-4 w-4 shrink-0 text-slate-300"></i>
                                @endif
                                <span>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        @php $isSubmitted = session('status') ? true : false; @endphp
                        <button type="button" onclick="document.querySelector('form').submit()" class="btn-primary-gradient w-full inline-flex items-center justify-center rounded-full px-8 py-4 text-lg font-semibold" @if($isSubmitted) disabled aria-disabled="true" @endif>
                            @if($isSubmitted) Submitted — waiting for approval @else Subscribe @endif
                        </button>
                    </div>
                </article>
            </aside>
        </div>
        </div>
    </div>
</div>
@endsection
