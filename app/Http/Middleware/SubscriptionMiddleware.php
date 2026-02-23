<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;

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
        // Check if user is authenticated first
        if (!session('authenticated')) {
            return redirect('/login')->with('error', 'Please login to access this feature.');
        }

        // Get current subscription
        $subscription = SubscriptionService::getCurrentSubscription();
        
        if (!$subscription) {
            return redirect('/pricing')->with('error', 'Please subscribe to access this feature.');
        }

        // Check if subscription is active
        if (!SubscriptionService::isActive()) {
            return redirect('/pricing')->with('error', 'Your subscription is not active. Please renew your plan.');
        }

        // Check specific feature access
        if ($requiredFeature) {
            if (!SubscriptionService::hasFeature($requiredFeature)) {
                return redirect('/pricing')->with('error', "This feature requires a higher subscription plan.");
            }
        }

        return $next($request);
    }
}
