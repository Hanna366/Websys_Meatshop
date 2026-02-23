<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredFeature = null)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect('/login')->with('error', 'Please login to access this feature.');
        }

        $subscription = Subscription::where('user_id', $user['id'])->first();
        
        if (!$subscription) {
            return redirect('/pricing')->with('error', 'Please subscribe to access this feature.');
        }

        // Check if subscription is active
        if ($subscription->status !== 'active') {
            return redirect('/pricing')->with('error', 'Your subscription is not active. Please renew your plan.');
        }

        // Check if subscription has expired
        if ($subscription->expires_at < now()) {
            $subscription->update(['status' => 'expired']);
            return redirect('/pricing')->with('error', 'Your subscription has expired. Please renew your plan.');
        }

        // Check specific feature access
        if ($requiredFeature) {
            if (!$this->hasFeatureAccess($subscription->plan, $requiredFeature)) {
                return redirect('/pricing')->with('error', "This feature requires a {$requiredFeature} plan or higher.");
            }
        }

        // Add subscription data to session for easy access
        session(['subscription' => $subscription]);

        return $next($request);
    }

    /**
     * Check if user has access to specific feature based on their plan
     */
    private function hasFeatureAccess($plan, $feature)
    {
        $features = [
            'basic' => [
                'products' => 100,
                'users' => 1,
                'pos' => false,
                'reports' => false,
                'export' => false,
                'api' => false,
                'analytics' => false,
                'custom_branding' => false,
                'priority_support' => false
            ],
            'standard' => [
                'products' => -1, // unlimited
                'users' => 3,
                'pos' => true,
                'reports' => true,
                'export' => true,
                'api' => false,
                'analytics' => false,
                'custom_branding' => false,
                'priority_support' => false
            ],
            'premium' => [
                'products' => -1, // unlimited
                'users' => -1, // unlimited
                'pos' => true,
                'reports' => true,
                'export' => true,
                'api' => true,
                'analytics' => true,
                'custom_branding' => true,
                'priority_support' => true
            ],
            'enterprise' => [
                'products' => -1, // unlimited
                'users' => -1, // unlimited
                'pos' => true,
                'reports' => true,
                'export' => true,
                'api' => true,
                'analytics' => true,
                'custom_branding' => true,
                'priority_support' => true,
                'dedicated_database' => true,
                'custom_integrations' => true,
                'sla_support' => true
            ]
        ];

        return isset($features[$plan][$feature]) && $features[$plan][$feature] === true;
    }

    /**
     * Get plan hierarchy for upgrade checks
     */
    private function getPlanHierarchy()
    {
        return ['basic' => 1, 'standard' => 2, 'premium' => 3, 'enterprise' => 4];
    }

    /**
     * Check if user can upgrade to a specific plan
     */
    public function canUpgrade($currentPlan, $targetPlan)
    {
        $hierarchy = $this->getPlanHierarchy();
        return $hierarchy[$targetPlan] > $hierarchy[$currentPlan];
    }

    /**
     * Check if user can downgrade to a specific plan
     */
    public function canDowngrade($currentPlan, $targetPlan)
    {
        $hierarchy = $this->getPlanHierarchy();
        return $hierarchy[$targetPlan] < $hierarchy[$currentPlan];
    }
}
