<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class SubscriptionService
{
    /**
     * Normalize plan names from session/user input.
     */
    public static function normalizePlan(string $plan): string
    {
        return strtolower(trim($plan));
    }

    /**
     * Map legacy feature keys to canonical plan feature keys.
     */
    public static function normalizeFeature(string $feature): string
    {
        $aliases = [
            'pos_access' => 'pos_system',
            'basic_reporting' => 'basic_reports',
        ];

        return $aliases[$feature] ?? $feature;
    }

    /**
     * Get current user's subscription
     */
    public static function getCurrentSubscription()
    {
        $user = session('user');
        
        if (!$user) {
            return null;
        }

        $plan = self::normalizePlan((string) ($user['plan'] ?? 'premium'));

        // Returns a deterministic subscription snapshot derived from session state.
        // This keeps middleware checks consistent while a DB-backed source is introduced.
        return [
            'id' => 1,
            'user_id' => $user['id'] ?? 'demo_user',
            'plan' => $plan,
            'price' => self::getPlanPricing()[$plan] ?? 149,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->addMonth(),
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

        return $features[$normalizedFeature] ?? false;
    }

    /**
     * Get plan features
     */
    public static function getPlanFeatures($plan)
    {
        $plan = self::normalizePlan((string) $plan);

        $features = [
            'basic' => [
                'inventory_tracking' => true,
                'stock_alerts' => true,
                'pos_system' => false,
                'supplier_management' => false,
                'customer_management' => false,
                'basic_reports' => false,
                'data_export' => false,
                'api_access' => false,
                'advanced_analytics' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'sms_notifications' => false
            ],
            'standard' => [
                'inventory_tracking' => true,
                'stock_alerts' => true,
                'pos_system' => true,
                'supplier_management' => true,
                'customer_management' => true,
                'basic_reports' => true,
                'data_export' => true,
                'api_access' => false,
                'advanced_analytics' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'sms_notifications' => false
            ],
            'premium' => [
                'inventory_tracking' => true,
                'stock_alerts' => true,
                'pos_system' => true,
                'supplier_management' => true,
                'customer_management' => true,
                'basic_reports' => true,
                'data_export' => true,
                'api_access' => true,
                'advanced_analytics' => true,
                'custom_branding' => true,
                'priority_support' => true,
                'sms_notifications' => true
            ],
            'enterprise' => [
                'inventory_tracking' => true,
                'stock_alerts' => true,
                'pos_system' => true,
                'supplier_management' => true,
                'customer_management' => true,
                'basic_reports' => true,
                'data_export' => true,
                'api_access' => true,
                'advanced_analytics' => true,
                'custom_branding' => true,
                'priority_support' => true,
                'sms_notifications' => true,
                'dedicated_database' => true,
                'custom_integrations' => true,
                'sla_support' => true,
                'on_premise_deployment' => true
            ]
        ];

        return $features[$plan] ?? [];
    }

    /**
     * Get plan hierarchy
     */
    public static function getPlanHierarchy()
    {
        return ['basic' => 1, 'standard' => 2, 'premium' => 3, 'enterprise' => 4];
    }

    /**
     * Check if user can upgrade to a specific plan
     */
    public static function canUpgrade($currentPlan, $targetPlan)
    {
        $hierarchy = self::getPlanHierarchy();
        return $hierarchy[$targetPlan] > $hierarchy[$currentPlan];
    }

    /**
     * Check if user can downgrade to a specific plan
     */
    public static function canDowngrade($currentPlan, $targetPlan)
    {
        $hierarchy = self::getPlanHierarchy();
        return $hierarchy[$targetPlan] < $hierarchy[$currentPlan];
    }

    /**
     * Get plan pricing
     */
    public static function getPlanPricing()
    {
        return [
            'basic' => 29,
            'standard' => 79,
            'premium' => 149,
            'enterprise' => null // custom pricing
        ];
    }

    /**
     * Get plan display name
     */
    public static function getPlanDisplayName($plan)
    {
        $plan = self::normalizePlan((string) $plan);

        $names = [
            'basic' => 'Basic',
            'standard' => 'Standard',
            'premium' => 'Premium',
            'enterprise' => 'Enterprise'
        ];

        return $names[$plan] ?? 'Unknown';
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

        // Simulate payment processing
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
