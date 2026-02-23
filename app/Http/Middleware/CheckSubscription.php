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
                'sms_notifications' => false,
                'max_users' => 1,
                'stock_alerts' => true,
                'csv_export' => false,
                'excel_export' => false,
                'pdf_export' => false,
                'dedicated_database' => false,
                'custom_integrations' => false,
                'sla_services' => false,
                'onpremise_deployment' => false,
                'compliance_tools' => false
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
                'sms_notifications' => false,
                'max_users' => 3,
                'stock_alerts' => true,
                'csv_export' => true,
                'excel_export' => false,
                'pdf_export' => false,
                'dedicated_database' => false,
                'custom_integrations' => false,
                'sla_services' => false,
                'onpremise_deployment' => false,
                'compliance_tools' => false
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
                'sms_notifications' => true,
                'max_users' => -1, // unlimited
                'stock_alerts' => true,
                'csv_export' => true,
                'excel_export' => true,
                'pdf_export' => true,
                'dedicated_database' => true,
                'custom_integrations' => true,
                'sla_services' => true,
                'onpremise_deployment' => true,
                'compliance_tools' => true
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
