<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    /**
     * Central billing page (web).
     */
    public function billingPage()
    {
        [$subscription, $billingHistory, $centralBillingMode] = $this->resolveBillingPayload();

        // If tenancy middleware didn't run for some reason (host routed to central),
        // attempt to initialize tenancy from the request host so tenant pages
        // still surface tenant-scoped data like pending subscription requests.
        $this->ensureTenantInitializedFromHost();

        $pendingRequest = null;
        if (!$centralBillingMode && app()->bound('tenant') && tenant()) {
            try {
                $pendingRequest = \App\Models\SubscriptionRequest::where('tenant_id', tenant()->tenant_id)
                    ->where('status', 'pending')
                    ->orderByDesc('created_at')
                    ->first();
            } catch (\Throwable $e) {
                $pendingRequest = null;
            }
        }

        return view('subscription.billing', [
            'subscription' => $subscription,
            'billingHistory' => $billingHistory,
            'centralBillingMode' => $centralBillingMode,
            'pending_request' => $pendingRequest ? [
                'id' => $pendingRequest->id,
                'payment_reference' => $pendingRequest->payment_reference,
                'amount' => $pendingRequest->amount,
                'requested_plan' => $pendingRequest->requested_plan,
                'created_at' => $pendingRequest->created_at->toDateTimeString(),
            ] : null,
            'pending_count' => $centralBillingMode ? \App\Models\SubscriptionRequest::where('status','pending')->count() : 0,
        ]);
    }

    /**
     * Billing data endpoint (JSON).
     */
    public function billingData()
    {
        [$subscription, $billingHistory, $centralBillingMode] = $this->resolveBillingPayload();

        // Ensure tenant initialization as above for JSON API calls used by tenant UI.
        $this->ensureTenantInitializedFromHost();

        $pendingRequest = null;
        if (!$centralBillingMode && app()->bound('tenant') && tenant()) {
            try {
                $pendingRequest = \App\Models\SubscriptionRequest::where('tenant_id', tenant()->tenant_id)
                    ->where('status', 'pending')
                    ->orderByDesc('created_at')
                    ->first();
            } catch (\Throwable $e) {
                $pendingRequest = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription,
                'history' => $billingHistory,
                'central_mode' => $centralBillingMode,
                'pending_request' => $pendingRequest ? [
                    'id' => $pendingRequest->id,
                    'payment_reference' => $pendingRequest->payment_reference,
                    'amount' => $pendingRequest->amount,
                    'requested_plan' => $pendingRequest->requested_plan,
                    'created_at' => $pendingRequest->created_at->toDateTimeString(),
                ] : null,
            ],
        ]);

    }

    /**
     * Try to initialize tenancy from the current request host when tenancy
     * hasn't been bound yet. Safe no-op if tenancy is already initialized.
     */
    private function ensureTenantInitializedFromHost(): void
    {
        if (app()->bound('tenant') && tenant()) {
            return;
        }

        try {
            $host = request()->getHost();
            if (! $host) {
                return;
            }

            $tenant = \App\Models\Tenant::where('domain', $host)->first();
            if ($tenant) {
                if (function_exists('tenancy')) {
                    tenancy()->initialize($tenant);
                } else {
                    app(\Stancl\Tenancy\Tenancy::class)->initialize($tenant);
                }
            }
        } catch (\Throwable $e) {
            // Don't block page on tenancy init failures; log for debugging.
            \Log::warning('Failed to initialize tenancy from host', ['host' => request()->getHost(), 'error' => $e->getMessage()]);
        }
    }

    private function resolveBillingPayload(): array
    {
        if ($this->isCentralContext()) {
            return $this->buildCentralBillingPayload();
        }

        return [
            SubscriptionService::getCurrentSubscription(),
            SubscriptionService::getBillingHistory(),
            false,
        ];
    }

    private function isCentralContext(): bool
    {
        if (tenant()) {
            return false;
        }

        return (string) session('auth_context', 'central') === 'central';
    }

    private function buildCentralBillingPayload(): array
    {
        $tenants = Tenant::query()->whereNull('deleted_at')->get();
        $pricing = SubscriptionService::getPlanPricing();

        $paidCount = 0;
        $unpaidCount = 0;
        $estimatedMrr = 0.0;
        $nextBillingAt = null;
        $history = [];

        foreach ($tenants as $tenant) {
            $planKey = SubscriptionService::normalizePlan((string) ($tenant->plan ?? data_get($tenant->subscription, 'plan', 'basic')));
            $amount = (float) ($pricing[$planKey] ?? 0);
            $estimatedMrr += $amount;

            $paymentStatus = strtolower((string) ($tenant->payment_status ?? 'paid'));
            if (in_array($paymentStatus, ['paid'], true)) {
                $paidCount++;
            }

            if (in_array($paymentStatus, ['unpaid', 'overdue'], true)) {
                $unpaidCount++;
            }

            $periodEnd = data_get($tenant->subscription, 'current_period_end')
                ?? optional($tenant->plan_ends_at)->toDateString();

            if ($periodEnd) {
                $periodEndDate = \Carbon\Carbon::parse((string) $periodEnd);
                if ($nextBillingAt === null || $periodEndDate->lt($nextBillingAt)) {
                    $nextBillingAt = $periodEndDate;
                }
            }

            $history[] = [
                'id' => 'TEN-' . strtoupper(substr((string) $tenant->tenant_id, 0, 8)),
                'tenant_name' => $tenant->business_name,
                'date' => optional($tenant->updated_at)->format('Y-m-d') ?? now()->format('Y-m-d'),
                'amount' => $amount,
                'plan' => ucfirst($planKey),
                'status' => ucfirst($paymentStatus),
                'payment_method' => 'N/A',
            ];
        }

        $status = $unpaidCount > 0 ? 'mixed' : 'healthy';

        $subscription = [
            'plan' => (string) $tenants->count(),
            'price' => $estimatedMrr,
            'status' => $status,
            'next_billing_at' => $nextBillingAt,
            'paid_tenants' => $paidCount,
            'unpaid_tenants' => $unpaidCount,
        ];

        return [$subscription, $history, true];
    }

    public function current()
    {
        return response()->json([
            'success' => true,
            'data' => ['subscription' => SubscriptionService::getCurrentSubscription()],
        ]);
    }

    public function plans()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pricing' => SubscriptionService::getPlanPricing(),
                'hierarchy' => SubscriptionService::getPlanHierarchy(),
            ],
        ]);
    }

    public function usage()
    {
        $subscription = SubscriptionService::getCurrentSubscription();

        return response()->json([
            'success' => true,
            'data' => [
                'days_until_expiration' => SubscriptionService::getDaysUntilExpiration(),
                'subscription' => $subscription,
            ],
        ]);
    }

    public function billing()
    {
        // Backward-compatible alias for existing API consumers.
        return $this->billingData();
    }

    public function create(Request $request)
    {
        return $this->processSubscription($request);
    }

    public function update(Request $request)
    {
        return $this->processSubscription($request);
    }

    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:credit_card,gcash,paypal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method update endpoint scaffolded',
            'data' => ['payment_method' => $request->payment_method],
        ]);
    }

    /**
     * Display subscription pricing page
     */
    public function index()
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return redirect('/login');
        }
        
        return view('pricing');
    }

    /**
     * Process subscription upgrade/downgrade
     */
    public function processSubscription(Request $request)
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'plan' => 'required|in:basic,standard,premium,enterprise',
            'payment_method' => 'required|in:credit_card,gcash,paypal'
        ]);

        $plan = $request->plan;
        $paymentMethod = $request->payment_method;

        // Process subscription via service
        $result = SubscriptionService::processSubscription($plan, $paymentMethod);
        
        if ($result['success']) {
            // If the request is pending central approval, return that state
            if (!empty($result['pending'])) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => $result['message'],
                    'payment_reference' => $result['payment_reference'] ?? null,
                ]);
            }
            // Update user session
            $user = session('user');
            $user['plan'] = SubscriptionService::getPlanDisplayName($plan);
            session(['user' => $user]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'plan' => $plan,
                'next_billing' => now()->addMonth()->format('F j, Y')
            ]);
        }

        return response()->json(['error' => $result['message']], 400);
    }

    /**
     * Tenant-only: create a subscription request (no payment yet).
     * This lets a tenant select a plan without navigating to central billing.
     */
    public function requestSubscription(Request $request)
    {
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'plan' => 'required|in:basic,standard,premium,enterprise',
        ]);

        $plan = $request->plan;

        try {
            if (!app()->bound('tenant') || !tenant()) {
                return response()->json(['error' => 'Not in tenant context'], 400);
            }

            $tenant = tenant();
            $pricing = SubscriptionService::getPlanPricing();
            $amount = (float) ($pricing[$plan] ?? 0);

            // Write to the central DB explicitly so tenant connection overrides do not
            // cause the insert to target the tenant database (which lacks the table).
            $centralConn = config('tenancy.database.central_connection', config('database.default'));
            $now = now();
            $id = \DB::connection($centralConn)->table('subscription_requests')->insertGetId([
                'tenant_id' => (string) $tenant->tenant_id,
                'requested_plan' => $plan,
                'payment_method' => null,
                'payment_reference' => null,
                'amount' => $amount,
                'status' => 'pending',
                'metadata' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $req = \DB::connection($centralConn)->table('subscription_requests')->where('id', $id)->first();

            try {
                app(\App\Services\NotificationService::class)->sendCentralApprovalRequest($tenant);
            } catch (\Throwable $e) {
                \Log::warning('Failed to notify central admin about subscription request', ['tenant' => $tenant->tenant_id, 'error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'pending' => true,
                'message' => 'Subscription change requested. Pending central approval.',
                'request_id' => $req->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to request subscription', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if (config('app.debug')) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }

            return response()->json(['error' => 'Unable to request subscription at this time'], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $result = SubscriptionService::cancelSubscription();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'expires_at' => now()->addMonth()->format('F j, Y')
            ]);
        }

        return response()->json(['error' => $result['message']], 400);
    }

    /**
     * Renew subscription
     */
    public function renew(Request $request)
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $result = SubscriptionService::renewSubscription();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'next_billing' => now()->addMonth()->format('F j, Y')
            ]);
        }

        return response()->json(['error' => $result['message']], 400);
    }

    /**
     * Get subscription status
     */
    public function status()
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = SubscriptionService::getCurrentSubscription();
        
        if (!$subscription) {
            return response()->json([
                'has_subscription' => false,
                'message' => 'No active subscription'
            ]);
        }

        $planFeatures = SubscriptionService::getPlanFeatures($subscription['plan']);

        return response()->json([
            'has_subscription' => true,
            'subscription' => [
                'plan' => $subscription['plan'],
                'plan_name' => SubscriptionService::getPlanDisplayName($subscription['plan']),
                'price' => $subscription['price'],
                'status' => $subscription['status'],
                'expires_at' => $subscription['expires_at']->format('F j, Y'),
                'days_until_expiration' => SubscriptionService::getDaysUntilExpiration(),
                'auto_renew' => $subscription['auto_renew'],
                'next_billing' => $subscription['next_billing_at']->format('F j, Y'),
                'features' => $planFeatures
            ]
        ]);
    }
    /**
     * Get billing history
     */
    public function billingHistory()
    {
        // Backward-compatible alias for older callers.
        return $this->billingData();
    }

    /**
     * Update subscription settings
     */
    public function updateSettings(Request $request)
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'auto_renew' => 'boolean',
            'payment_method' => 'sometimes|in:credit_card,gcash,paypal'
        ]);

        // In a real implementation, this would update the database
        // For demo purposes, just return success
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }
}
