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

        // Convert configured plan prices (assumed USD) to PHP for tenant display.
        // Use PESO_RATE from env (default 55) so UI shows ₱ amounts consistently.
        $pesoRate = (float) env('PESO_RATE', 55);
        if (!empty($definition)) {
            $monthly = (float) data_get($definition, 'price_monthly', 0);
            // Some plan configs use a different key for annual monthly price
            $annualMonthly = (float) data_get($definition, 'price_annual', data_get($definition, 'price_annual_monthly', 0));
            $definition['price_monthly'] = round($monthly * $pesoRate, 2);
            if ($annualMonthly > 0) {
                $definition['price_annual'] = round($annualMonthly * $pesoRate, 2);
            } else {
                $definition['price_annual'] = round(($monthly * 12) * $pesoRate, 2);
            }
        }

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
        // Determine configured price (assumed USD) then convert to PHP for
        // simulated checkout. We do not call any external payment provider
        // here — this is a mock/manual flow where tenants submit proof.
        $configMonthly = (float) data_get($plans, "{$data['plan']}.price_monthly", 0);
        $configAnnualMonthly = data_get($plans, "{$data['plan']}.price_annual", data_get($plans, "{$data['plan']}.price_annual_monthly", null));

        if ($data['billing_cycle'] === 'annual') {
            if (!is_null($configAnnualMonthly)) {
                $price = (float) $configAnnualMonthly * 12;
            } else {
                $price = $configMonthly * 12;
            }
        } else {
            $price = $configMonthly;
        }

        // Convert to PHP for display/storage using PESO_RATE (env fallback 55)
        $pesoRate = (float) env('PESO_RATE', 55);
        $price = round($price * $pesoRate, 2);

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

        // If the user is authenticated, send them to the payments history.
        // If not authenticated (guest), return back with a flash message so
        // they are not redirected to the login page and instead see a
        // confirmation that their manual payment is pending approval.
        if (auth()->check()) {
            return redirect()->route('tenant.payments.history')->with('status', 'Payment submitted. Waiting for admin approval.');
        }

        return back()->with('status', 'Payment submitted. Waiting for admin approval.');
    }

    public function history()
    {
        $tenant = tenant();
        $payments = SubscriptionPayment::where('tenant_id', $tenant->id)->orderBy('created_at', 'desc')->get();

        return view('tenant.payments.history', ['payments' => $payments]);
    }
}
