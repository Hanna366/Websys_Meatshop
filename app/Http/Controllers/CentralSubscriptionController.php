<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CentralSubscriptionController extends Controller
{
    public function index()
    {
        $requests = SubscriptionRequest::query()->orderByDesc('created_at')->limit(200)->get();

        return view('admin.subscription_requests.index', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, int $id, NotificationService $notificationService)
    {
        $req = SubscriptionRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            return redirect()->back()->with('error', 'Request is not pending.');
        }

        $tenant = Tenant::where('tenant_id', $req->tenant_id)->first();
        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant not found.');
        }

        // Apply plan change centrally
        $tenant->plan = $req->requested_plan;
        $tenant->subscription = array_merge(is_array($tenant->subscription) ? $tenant->subscription : [], [
            'plan' => $req->requested_plan,
            'status' => 'active',
            'current_period_start' => now()->toDateString(),
            'current_period_end' => now()->copy()->addMonth()->toDateString(),
        ]);
        $tenant->payment_status = 'paid';
        $tenant->save();

        $req->status = 'approved';
        $req->save();

        // Notify tenant about approval
        try {
            $notificationService->sendSubscriptionUpdated($tenant);
        } catch (\Throwable $e) {
            \Log::warning('Failed to notify tenant about subscription approval', ['tenant' => $tenant->tenant_id, 'error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Subscription request approved and applied.');
    }

    public function reject(Request $request, int $id)
    {
        $req = SubscriptionRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            return redirect()->back()->with('error', 'Request is not pending.');
        }

        $req->status = 'rejected';
        $req->save();

        return redirect()->back()->with('success', 'Subscription request rejected.');
    }
}
