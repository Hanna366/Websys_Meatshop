<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class DevDebugController extends Controller
{
    public function subscription(Request $request)
    {
        if (!config('app.debug')) {
            abort(404);
        }

        $tenant = app()->bound('tenant') && tenant() ? tenant() : null;

        return response()->json([
            'tenant' => $tenant ? $tenant->only(['tenant_id', 'business_name', 'plan', 'status', 'subscription']) : null,
            'session_authenticated' => session('authenticated', false),
            'session_auth_context' => session('auth_context', null),
            'session_user' => session('user', null),
            'resolved_plan' => SubscriptionService::resolveCurrentPlan(),
            'current_subscription_snapshot' => SubscriptionService::getCurrentSubscription(),
            'plan_features' => SubscriptionService::getPlanFeatures(SubscriptionService::resolveCurrentPlan()),
        ]);
    }
}
