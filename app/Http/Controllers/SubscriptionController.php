<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\SubscriptionService;

class SubscriptionController extends Controller
{
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
        return response()->json([
            'success' => true,
            'data' => ['history' => SubscriptionService::getBillingHistory()],
        ]);
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
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $billingHistory = SubscriptionService::getBillingHistory();

        return response()->json([
            'billing_history' => $billingHistory
        ]);
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
