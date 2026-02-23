<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;

class SubscriptionController extends Controller
{
    /**
     * Display subscription pricing page
     */
    public function index()
    {
        return view('pricing');
    }

    /**
     * Process subscription upgrade/downgrade
     */
    public function processSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:basic,standard,premium,enterprise',
            'payment_method' => 'required|in:credit_card,gcash,paypal'
        ]);

        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Get current subscription
        $currentSubscription = Subscription::where('user_id', $user['id'])->first();
        
        // Plan pricing
        $planPrices = [
            'basic' => 29,
            'standard' => 79,
            'premium' => 149,
            'enterprise' => null // custom pricing
        ];

        $plan = $request->plan;
        $price = $planPrices[$plan];

        // Handle enterprise plan (contact sales)
        if ($plan === 'enterprise') {
            return $this->handleEnterpriseInquiry($request);
        }

        // Process payment (simulation)
        $paymentResult = $this->processPayment($request->payment_method, $price);
        
        if (!$paymentResult['success']) {
            return response()->json(['error' => $paymentResult['message']], 400);
        }

        // Create or update subscription
        if ($currentSubscription) {
            // Update existing subscription
            $currentSubscription->update([
                'plan' => $plan,
                'price' => $price,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'payment_method' => $request->payment_method,
                'last_payment_at' => now(),
                'next_billing_at' => now()->addMonth(),
                'auto_renew' => true,
                'subscription_id' => $paymentResult['subscription_id']
            ]);
        } else {
            // Create new subscription
            Subscription::create([
                'user_id' => $user['id'],
                'plan' => $plan,
                'price' => $price,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'payment_method' => $request->payment_method,
                'last_payment_at' => now(),
                'next_billing_at' => now()->addMonth(),
                'auto_renew' => true,
                'subscription_id' => $paymentResult['subscription_id']
            ]);
        }

        // Update user session
        $user['plan'] = ucfirst($plan);
        session(['user' => $user]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully!',
            'plan' => $plan,
            'next_billing' => now()->addMonth()->format('F j, Y')
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = Subscription::where('user_id', $user['id'])->first();
        
        if (!$subscription) {
            return response()->json(['error' => 'No active subscription found'], 404);
        }

        $subscription->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'expires_at' => $subscription->expires_at->format('F j, Y')
        ]);
    }

    /**
     * Renew subscription
     */
    public function renew(Request $request)
    {
        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = Subscription::where('user_id', $user['id'])->first();
        
        if (!$subscription) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        // Process payment for renewal
        $paymentResult = $this->processPayment($subscription->payment_method, $subscription->price);
        
        if (!$paymentResult['success']) {
            return response()->json(['error' => $paymentResult['message']], 400);
        }

        $subscription->renew();

        return response()->json([
            'success' => true,
            'message' => 'Subscription renewed successfully',
            'next_billing' => $subscription->next_billing_at->format('F j, Y')
        ]);
    }

    /**
     * Get subscription status
     */
    public function status()
    {
        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = Subscription::where('user_id', $user['id'])->first();
        
        if (!$subscription) {
            return response()->json([
                'has_subscription' => false,
                'message' => 'No active subscription'
            ]);
        }

        $planFeatures = $subscription->getPlanFeatures();

        return response()->json([
            'has_subscription' => true,
            'subscription' => [
                'plan' => $subscription->plan,
                'plan_name' => $planFeatures['name'],
                'price' => $subscription->price,
                'status' => $subscription->status,
                'expires_at' => $subscription->expires_at->format('F j, Y'),
                'days_until_expiration' => $subscription->getDaysUntilExpiration(),
                'auto_renew' => $subscription->auto_renew,
                'next_billing' => $subscription->next_billing_at->format('F j, Y'),
                'features' => $planFeatures['features']
            ]
        ]);
    }

    /**
     * Handle enterprise plan inquiry
     */
    private function handleEnterpriseInquiry($request)
    {
        // Store inquiry in database or send email
        // For now, just return success message
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for your interest! Our enterprise sales team will contact you within 24 hours.'
        ]);
    }

    /**
     * Process payment (simulation)
     */
    private function processPayment($method, $amount)
    {
        // Simulate payment processing
        // In real implementation, integrate with actual payment gateways
        
        try {
            // Simulate API call delay
            usleep(100000); // 0.1 seconds

            // Generate fake subscription ID
            $subscriptionId = 'sub_' . uniqid();

            return [
                'success' => true,
                'subscription_id' => $subscriptionId,
                'message' => 'Payment processed successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get billing history
     */
    public function billingHistory()
    {
        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Simulate billing history
        $billingHistory = [
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

        return response()->json([
            'billing_history' => $billingHistory
        ]);
    }

    /**
     * Update subscription settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'auto_renew' => 'boolean',
            'payment_method' => 'sometimes|in:credit_card,gcash,paypal'
        ]);

        $user = session('user');
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = Subscription::where('user_id', $user['id'])->first();
        
        if (!$subscription) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        $subscription->update($request->only(['auto_renew', 'payment_method']));

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }
}
