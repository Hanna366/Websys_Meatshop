<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Domain;
use Illuminate\Support\Facades\Log;
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

        // If request comes from a tenant flow wanting to create a subscription
        // request centrally (fallback), handle query param and create a pending
        // subscription request record for central admins to review.
        if (request()->query('create_subscription_request') && request()->query('tenant_host') && request()->query('plan')) {
            $tenantHost = request()->query('tenant_host');
            $plan = request()->query('plan');
            try {
                $domain = \App\Models\Domain::where('domain', $tenantHost)->first();
                $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $tenantHost)->first();
                if ($tenant) {
                    $centralConn = config('tenancy.database.central_connection', config('database.default'));
                    $now = now();
                    $id = \DB::connection($centralConn)->table('subscription_requests')->insertGetId([
                        'tenant_id' => (string) $tenant->tenant_id,
                        'requested_plan' => $plan,
                        'payment_method' => null,
                        'payment_reference' => null,
                        'amount' => (float) (\App\Services\SubscriptionService::getPlanPricing()[$plan] ?? 0),
                        'status' => 'pending',
                        'metadata' => json_encode(['via' => 'tenant_fallback']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    try { app(\App\Services\NotificationService::class)->sendCentralApprovalRequest($tenant); } catch (\Throwable $e) {}
                    session()->flash('status', 'Subscription request created and pending central approval.');
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to create fallback subscription request', ['tenant_host' => $tenantHost, 'error' => $e->getMessage()]);
            }
        }

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

            $tenant = null;

            // First try domains table lookup (preferred stancl/tenancy pattern)
            if (\Illuminate\Support\Facades\Schema::hasTable('domains')) {
                $domain = \App\Models\Domain::where('domain', $host)->first();
                if ($domain && $domain->tenant) {
                    $tenant = $domain->tenant;
                }
            }

            // Fallback: some installations store domain on tenants table
            if (! $tenant && \Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'domain')) {
                $tenant = \App\Models\Tenant::where('domain', $host)->first();
            }

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
        // Log incoming request info to help diagnose central vs tenant rendering.
        try {
            \Log::info('SubscriptionController@index called', [
                'host' => request()->getHost(),
                'full_url' => request()->fullUrl(),
                'tenant_bound_before' => (bool) (app()->bound('tenant') && tenant()),
                'session_auth_context' => session('auth_context', null),
            ]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        // Try to initialize tenancy from the request host so the pricing page
        // renders within the tenant context when accessed via a tenant domain.
        $this->ensureTenantInitializedFromHost();

        try {
            \Log::info('SubscriptionController@index after ensureTenantInitializedFromHost', [
                'host' => request()->getHost(),
                'tenant_bound_after' => (bool) (app()->bound('tenant') && tenant()),
                'tenant_id' => app()->bound('tenant') && tenant() ? tenant()->tenant_id : null,
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        // Allow central pages to pass ?tenant=<uuid> or ?tenant_host=<host>
        // so the pricing view can act on behalf of that tenant.
        $forcedTenantHost = null;
        try {
            if (request()->filled('tenant')) {
                $t = \App\Models\Tenant::where('tenant_id', request()->query('tenant'))->orWhere('id', request()->query('tenant'))->first();
                if ($t) {
                    $forcedTenantHost = $t->domain ?? null;
                    if (! $forcedTenantHost) {
                        // try domains table
                        $d = \App\Models\Domain::where('tenant_id', $t->id)->first();
                        if ($d) $forcedTenantHost = $d->domain;
                    }
                }
            } elseif (request()->filled('tenant_host')) {
                $forcedTenantHost = request()->query('tenant_host');
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return view('pricing', ['forcedTenantHost' => $forcedTenantHost]);
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

        // Ensure tenancy is initialized from the request host if it hasn't
        // been bound yet. This helps when requests arrive via a central
        // rendering but should operate in tenant context.
        $this->ensureTenantInitializedFromHost();

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
     * Public variant of requestSubscription for tenant-origin requests.
     * Does not require session authentication but still validates input
     * and writes a pending request to the central DB after tenancy is
     * initialized by the caller.
     */
    public function requestSubscriptionPublic(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:basic,standard,premium,enterprise',
        ]);

        try {
            Log::info('requestSubscriptionPublic called', ['payload' => $request->all(), 'host' => $request->getHost()]);
            // Resolve tenant: prefer bound tenant, otherwise accept tenant_id or tenant_host
            $tenant = null;
            if (app()->bound('tenant') && tenant()) {
                $tenant = tenant();
            } else {
                if ($request->filled('tenant_id')) {
                    $tenant = Tenant::where('tenant_id', $request->input('tenant_id'))->first();
                }

                if (!$tenant && $request->filled('tenant_host')) {
                    // Try domains table first (stancl tenancy domains), then tenant.domain column
                    $domain = Domain::where('domain', $request->input('tenant_host'))->first();
                    if ($domain && $domain->tenant) {
                        $tenant = $domain->tenant;
                    } else {
                        $tenant = Tenant::where('domain', $request->input('tenant_host'))->first();
                    }
                }

                // As a final attempt, try the Host header
                if (!$tenant) {
                    $hostHeader = $request->header('X-Tenant-Host') ?? $request->header('Host');
                    if ($hostHeader) {
                        $domain = Domain::where('domain', $hostHeader)->first();
                        if ($domain && $domain->tenant) {
                            $tenant = $domain->tenant;
                        } else {
                            $tenant = Tenant::where('domain', $hostHeader)->first();
                        }
                    }
                }
            }

            if (!$tenant) {
                Log::warning('requestSubscriptionPublic: tenant not found', ['payload' => $request->all(), 'host' => $request->getHost()]);
                return response()->json(['error' => 'Tenant not found'], 400);
            }

            $pricing = SubscriptionService::getPlanPricing();
            $amount = (float) ($pricing[$request->plan] ?? 0);

            $centralConn = config('tenancy.database.central_connection', config('database.default'));
            $now = now();
            
            // Use the model to create the request with correct pricing
            $pesoRate = env('PESO_RATE', 55);
            $planPricing = [
                'basic' => 29 * $pesoRate,     // ₱1,595
                'standard' => 79 * $pesoRate,  // ₱4,345  
                'premium' => 149 * $pesoRate,  // ₱8,195
            ];
            $correctAmount = (float) ($planPricing[$request->plan] ?? $amount);
            
            $subscriptionRequest = new \App\Models\SubscriptionRequest();
            $subscriptionRequest->tenant_id = (string) $tenant->tenant_id;
            $subscriptionRequest->requested_plan = $request->plan;
            $subscriptionRequest->payment_method = null;
            $subscriptionRequest->payment_reference = null;
            $subscriptionRequest->amount = $correctAmount;
            $subscriptionRequest->status = 'pending';
            $subscriptionRequest->metadata = json_encode(['source' => 'pricing_page']);
            $subscriptionRequest->created_at = $now;
            $subscriptionRequest->updated_at = $now;
            $subscriptionRequest->save();
            
            $id = $subscriptionRequest->id;

            Log::info('Created central subscription_request', ['id' => $id, 'tenant' => $tenant->tenant_id, 'plan' => $request->plan, 'amount' => $correctAmount, 'source' => 'pricing_page']);

            $req = \DB::connection($centralConn)->table('subscription_requests')->where('id', $id)->first();
            
            Log::info('Retrieved subscription_request', ['request' => $req]);

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
            \Log::error('Failed to request subscription (public)', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
