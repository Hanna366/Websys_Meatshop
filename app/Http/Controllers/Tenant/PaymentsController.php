<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\SubscriptionService;

class PaymentsController extends Controller
{
    public function checkout(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        $billing = $request->query('billing', 'monthly');

        $plans = (array) config('plans.definitions', ['basic'=>[],'standard'=>[],'premium'=>[]]);
        $definition = $plans[$plan] ?? [];

        return view('tenant.payments.checkout', [
            'plan_key' => $plan,
            'plan' => $definition,
            'billing' => $billing,
        ]);
    }

    public function store(Request $request)
    {
        $tenant = tenant();

        $data = $request->validate([
            'plan' => 'required|string',
            'billing_cycle' => 'required|string',
            'payment_method' => 'required|string',
            'reference_number' => 'required_if:payment_method,GCash,Bank Transfer|string|max:255',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string',
        ], [
            'payment_method.required' => 'Payment method is required.',
            'reference_number.required_if' => 'Reference number is required for GCash or Bank Transfer.',
            'proof.required' => 'Proof of payment is required.',
            'proof.mimes' => 'File must be JPG, PNG, or PDF.',
            'proof.max' => 'Uploaded file must be smaller than 5MB.',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store("payments/{$tenant->id}", 'public');
        }

        $plans = (array) config('plans.definitions', []);
        $price = data_get($plans, "{$data['plan']}.price_monthly", 0);
        if ($data['billing_cycle'] === 'annual') {
            $price = data_get($plans, "{$data['plan']}.price_annual", $price * 12);
        }

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id() ?? null,
            'plan_id' => $data['plan'],
            'amount' => $price,
            'billing_cycle' => $data['billing_cycle'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'proof_path' => $proofPath,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending'
        ]);

        return redirect()->route('tenant.payments.history')->with('status', 'Payment submitted. Waiting for admin approval.');
    }

    public function history()
    {
        $tenant = tenant();
        $payments = SubscriptionPayment::where('tenant_id', $tenant->id)->orderBy('created_at', 'desc')->get();

        return view('tenant.payments.history', ['payments' => $payments]);
    }
}
