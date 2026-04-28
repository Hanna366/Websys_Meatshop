<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CentralSubscriptionController extends Controller
{
    public function index()
    {
        // Get ALL requests without any filters - force fresh data
        $requests = SubscriptionRequest::orderByDesc('created_at')->get();

        return view('admin.subscription_requests.index', [
            'requests' => $requests,
        ]);
    }

    /**
     * Return new pending subscription requests created after the provided id.
     * Used by the admin UI to poll for live updates.
     */
    public function updates(Request $request)
    {
        $since = (int) $request->query('since_id', 0);
        
        Log::info('Subscription updates polling', ['since_id' => $since]);

        $new = SubscriptionRequest::query()
            ->where('status', 'pending')
            ->when($since > 0, function ($q) use ($since) {
                $q->where('id', '>', $since);
            })
            ->orderBy('id')
            ->get();
            
        Log::info('Found subscription requests', ['count' => $new->count(), 'requests' => $new->toArray()]);

        return response()->json([
            'success' => true,
            'data' => $new->map(function ($r) {
                return [
                    'id' => $r->id,
                    'tenant_id' => $r->tenant_id,
                    'requested_plan' => $r->requested_plan,
                    'amount' => number_format($r->amount, 2),
                    'payment_reference' => $r->payment_reference,
                    'created_at' => $r->created_at->format('Y-m-d H:i'),
                    'status' => $r->status,
                ];
            }),
        ]);
    }

    public function approve(Request $request, int $id, NotificationService $notificationService)
    {
        $req = SubscriptionRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request is not pending.']);
            }
            return redirect()->back()->with('error', 'Request is not pending.');
        }

        $tenant = Tenant::where('tenant_id', $req->tenant_id)->first();
        if (!$tenant) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tenant not found.']);
            }
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Subscription request approved and applied.']);
        }
        return redirect()->back()->with('success', 'Subscription request approved and applied.');
    }

    public function reject(Request $request, int $id)
    {
        $req = SubscriptionRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request is not pending.']);
            }
            return redirect()->back()->with('error', 'Request is not pending.');
        }

        $req->status = 'rejected';
        $req->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Subscription request rejected.']);
        }
        return redirect()->back()->with('success', 'Subscription request rejected.');
    }
}
