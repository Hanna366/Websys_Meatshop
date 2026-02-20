<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $requiredFeature = null)
    {
        $userPlan = session('user.plan', 'Basic');
        
        // Define feature permissions for each plan
        $permissions = [
            'Basic' => [
                'max_products' => 100,
                'pos_access' => false,
                'customer_management' => false,
                'supplier_management' => false,
                'data_export' => false,
                'advanced_analytics' => false,
                'api_access' => false,
                'batch_operations' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'max_users' => 1
            ],
            'Standard' => [
                'max_products' => -1, // unlimited
                'pos_access' => true,
                'customer_management' => true,
                'supplier_management' => true,
                'data_export' => true,
                'advanced_analytics' => false,
                'api_access' => false,
                'batch_operations' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'max_users' => 3
            ],
            'Premium' => [
                'max_products' => -1, // unlimited
                'pos_access' => true,
                'customer_management' => true,
                'supplier_management' => true,
                'data_export' => true,
                'advanced_analytics' => true,
                'api_access' => true,
                'batch_operations' => true,
                'custom_branding' => true,
                'priority_support' => true,
                'max_users' => -1 // unlimited
            ]
        ];
        
        $userPermissions = $permissions[$userPlan] ?? $permissions['Basic'];
        
        // Store permissions in session for use in views
        session(['permissions' => $userPermissions]);
        
        // Check specific feature access if required
        if ($requiredFeature && !$userPermissions[$requiredFeature]) {
            return redirect('/dashboard')->with('error', 'This feature requires ' . $requiredFeature . ' access. Upgrade your plan to unlock this feature.');
        }
        
        return $next($request);
    }
}
