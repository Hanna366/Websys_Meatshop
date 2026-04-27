<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = SubscriptionPayment::query()->orderBy('created_at', 'desc');
        if ($status) {
            $q->where('status', $status);
        }

        $payments = $q->paginate(25);

        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function show($id)
    {
        $payment = SubscriptionPayment::findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }

    public function approve(Request $request, $id)
    {
        $payment = SubscriptionPayment::findOrFail($id);
        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending payments can be approved.');
        }

        $tenant = Tenant::find($payment->tenant_id);
        if (! $tenant) {
            return redirect()->back()->with('error', 'Tenant not found.');
        }

        // Apply plan to tenant
        $tenant->plan = $payment->plan_id;
        $tenant->plan_started_at = now();
        if ($payment->billing_cycle === 'annual') {
            $tenant->plan_ends_at = now()->addYear();
        } else {
            $tenant->plan_ends_at = now()->addMonth();
        }
        $tenant->payment_status = 'paid';
        $tenant->save();

        $payment->status = 'approved';
        $payment->reviewed_by = Auth::id();
        $payment->reviewed_at = now();
        $payment->admin_notes = $request->input('admin_notes');
        $payment->save();

        return redirect()->route('admin.payments.index')->with('status', 'Payment approved and plan updated.');
    }

    public function reject(Request $request, $id)
    {
        $payment = SubscriptionPayment::findOrFail($id);
        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending payments can be rejected.');
        }

        $payment->status = 'rejected';
        $payment->reviewed_by = Auth::id();
        $payment->reviewed_at = now();
        $payment->admin_notes = $request->input('admin_notes');
        $payment->save();

        return redirect()->route('admin.payments.index')->with('status', 'Payment rejected.');
    }
}
