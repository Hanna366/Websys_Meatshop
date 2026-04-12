<?php

namespace App\Services;

class SubscriptionService
{
    /**
     * Normalize plan names from session/user input.
     */
    public static function normalizePlan(string $plan): string
    {
        $normalized = strtolower(trim($plan));
        return array_key_exists($normalized, self::getPlanDefinitions()) ? $normalized : 'basic';
    }

    /**
     * Map legacy feature keys to canonical plan feature keys.
     */
    public static function normalizeFeature(string $feature): string
    {
        $aliases = [
            'pos_access' => 'pos_system',
            'basic_reporting' => 'basic_reports',
            'advanced_reporting' => 'advanced_analytics',
        ];

        return $aliases[$feature] ?? $feature;
    }

    /**
     * Resolve active plan from tenant context first, then session.
     */
    public static function resolveCurrentPlan(): string
    {
        $tenant = tenant();

        if ($tenant) {
            $tenantPlan = (string) ($tenant->plan ?? data_get($tenant->subscription, 'plan', 'basic'));
            return self::normalizePlan($tenantPlan);
        }

        return self::normalizePlan((string) data_get(session('user', []), 'plan', 'basic'));
    }

    /**
     * Get configured plan definitions.
     */
    public static function getPlanDefinitions(): array
    {
        return (array) config('plans.definitions', []);
    }

    /**
     * Get current user's subscription
     */
    public static function getCurrentSubscription()
    {
        // Allow deriving a subscription for tenant context even when session auth
        // is not present (useful for tenant-scoped UI that shows plan features).
        if (!session('authenticated') && !app()->bound('tenant')) {
            return null;
        }

        $plan = self::resolveCurrentPlan();
        $priceMap = self::getPlanPricing();
        $tenantSubscription = [];

        $tenant = tenant();
        if ($tenant) {
            $tenantSubscription = is_array($tenant->subscription) ? $tenant->subscription : [];
        }

        $status = (string) ($tenantSubscription['status'] ?? 'active');
        $periodStart = $tenantSubscription['current_period_start'] ?? now()->subMonth();
        $periodEnd = $tenantSubscription['current_period_end'] ?? now()->addMonth();

        // Returns a deterministic subscription snapshot derived from session state.
        // This keeps middleware checks consistent while a DB-backed source is introduced.
        return [
            'id' => 1,
            'user_id' => data_get(session('user', []), 'id', 'demo_user'),
            'plan' => $plan,
            'price' => $priceMap[$plan] ?? null,
            'status' => $status,
            'starts_at' => $periodStart,
            'expires_at' => $periodEnd,
            'payment_method' => 'credit_card',
            'last_payment_at' => now()->subMonth(),
            'next_billing_at' => now()->addMonth(),
            'auto_renew' => true,
            'features_used' => [],
            'subscription_id' => 'sub_demo_123'
        ];
    }

    /**
     * Check if user has access to a specific feature
     */
    public static function hasFeature($feature)
    {
        $subscription = self::getCurrentSubscription();
        
        if (!$subscription || $subscription['status'] !== 'active') {
            return false;
        }

        $features = self::getPlanFeatures($subscription['plan']);
        $normalizedFeature = self::normalizeFeature((string) $feature);

        return (bool) ($features[$normalizedFeature] ?? false);
    }

    /**
     * Get plan features
     */
    public static function getPlanFeatures($plan)
    {
        $plan = self::normalizePlan((string) $plan);

        return (array) data_get(self::getPlanDefinitions(), "{$plan}.features", []);
    }

    /**
     * Get plan limits.
     */
    public static function getPlanLimits(string $plan): array
    {
        $plan = self::normalizePlan($plan);
        return (array) data_get(self::getPlanDefinitions(), "{$plan}.limits", []);
    }

    /**
     * Evaluate if a usage count is within plan limit.
     */
    public static function isWithinLimit(string $limitKey, int $currentUsage, ?string $plan = null): bool
    {
        $activePlan = self::normalizePlan((string) ($plan ?? self::resolveCurrentPlan()));
        $limitValue = self::getPlanLimits($activePlan)[$limitKey] ?? null;

        if ($limitValue === null) {
            return true;
        }

        return $currentUsage <= (int) $limitValue;
    }

    /**
     * Get plan hierarchy
     */
    public static function getPlanHierarchy()
    {
        $definitions = array_keys(self::getPlanDefinitions());
        $hierarchy = [];
        $level = 1;

        foreach ($definitions as $planName) {
            $hierarchy[$planName] = $level;
            $level++;
        }

        return $hierarchy;
    }

    /**
     * Check if user can upgrade to a specific plan
     */
    public static function canUpgrade($currentPlan, $targetPlan)
    {
        $hierarchy = self::getPlanHierarchy();
        return ($hierarchy[$targetPlan] ?? 0) > ($hierarchy[$currentPlan] ?? 0);
    }

    /**
     * Check if user can downgrade to a specific plan
     */
    public static function canDowngrade($currentPlan, $targetPlan)
    {
        $hierarchy = self::getPlanHierarchy();
        return ($hierarchy[$targetPlan] ?? 0) < ($hierarchy[$currentPlan] ?? 0);
    }

    /**
     * Get plan pricing
     */
    public static function getPlanPricing()
    {
        $pricing = [];

        foreach (self::getPlanDefinitions() as $plan => $definition) {
            $pricing[$plan] = $definition['price_monthly'] ?? null;
        }

        return $pricing;
    }

    /**
     * Get plan display name
     */
    public static function getPlanDisplayName($plan)
    {
        $plan = self::normalizePlan((string) $plan);

        return (string) data_get(self::getPlanDefinitions(), "{$plan}.label", 'Unknown');
    }

    /**
     * Get plan badge color
     */
    public static function getPlanBadgeColor($plan)
    {
        $colors = [
            'basic' => 'primary',
            'standard' => 'warning',
            'premium' => 'danger',
            'enterprise' => 'dark'
        ];

        return $colors[$plan] ?? 'secondary';
    }

    /**
     * Check if subscription is active
     */
    public static function isActive()
    {
        $subscription = self::getCurrentSubscription();
        
        if (!$subscription) {
            return false;
        }

        return $subscription['status'] === 'active' &&
               now()->lt($subscription['expires_at']);
    }

    /**
     * Get days until expiration
     */
    public static function getDaysUntilExpiration()
    {
        $subscription = self::getCurrentSubscription();
        
        if (!$subscription || !$subscription['expires_at']) {
            return 0;
        }

        return now()->diffInDays($subscription['expires_at'], false);
    }

    /**
     * Get billing history (demo data)
     */
    public static function getBillingHistory()
    {
        return [
            [
                'id' => 'INV-001',
                'date' => '2026-01-15',
                'amount' => 149,
                'plan' => 'Premium',
                'status' => 'Paid',
                'payment_method' => 'Credit Card'
            ],
            [
                'id' => 'INV-002',
                'date' => '2026-02-15',
                'amount' => 149,
                'plan' => 'Premium',
                'status' => 'Paid',
                'payment_method' => 'Credit Card'
            ]
        ];
    }

    /**
     * Process subscription upgrade/downgrade
     */
    public static function processSubscription($plan, $paymentMethod)
    {
        $pricing = self::getPlanPricing();
        $price = $pricing[$plan];

        if ($plan === 'enterprise') {
            return [
                'success' => false,
                'message' => 'Please contact sales for enterprise pricing'
            ];
        }

        // If we're in a tenant context, create a central subscription request
        // and require central approval when a payment was made.
        try {
            $tenant = null;
            if (app()->bound('tenant') && tenant()) {
                $tenant = tenant();
            }

            // Simulate payment only for tenants (demo). In real world integrate gateway here.
            $paymentReference = 'pay_' . uniqid();

            if ($tenant) {
                // Record the request centrally so central admins can approve
                $centralConn = config('tenancy.database.central_connection', config('database.default'));
                $now = now();
                \DB::connection($centralConn)->table('subscription_requests')->insert([
                    'tenant_id' => (string) $tenant->tenant_id,
                    'requested_plan' => $plan,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $paymentReference,
                    'amount' => (float) $price,
                    'status' => 'pending',
                    'metadata' => json_encode([]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Notify central admins about the pending approval
                try {
                    app(\App\Services\NotificationService::class)->sendCentralApprovalRequest($tenant);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to notify central admin about subscription request', ['tenant' => $tenant->tenant_id, 'error' => $e->getMessage()]);
                }

                return [
                    'success' => true,
                    'pending' => true,
                    'payment_reference' => $paymentReference,
                    'message' => 'Subscription change requested. Pending central approval.'
                ];
            }

            // Central context: apply immediately (demo behavior)
            $subscriptionId = 'sub_' . uniqid();

            // Update session with new subscription
            $user = session('user');
            $user['plan'] = ucfirst($plan);
            session(['user' => $user]);

            return [
                'success' => true,
                'subscription_id' => $subscriptionId,
                'message' => 'Subscription updated successfully'
            ];
        } catch (\Throwable $e) {
            \Log::error('Failed to process subscription request', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Unable to process subscription request at this time'];
        }
    }

    /**
     * Cancel subscription
     */
    public static function cancelSubscription()
    {
        // In production, this would update the database
        // For demo, just return success
        return [
            'success' => true,
            'message' => 'Subscription cancelled successfully'
        ];
    }

    /**
     * Renew subscription
     */
    public static function renewSubscription()
    {
        // In production, this would process payment and update database
        return [
            'success' => true,
            'message' => 'Subscription renewed successfully'
        ];
    }
}
