<?php

namespace App\Services;

class EntitlementService
{
    /**
     * Centralized feature-access decision for web flows.
     */
    public static function canAccess(?string $requiredFeature = null): array
    {
        if (!session('authenticated')) {
            return [
                'allowed' => false,
                'redirect' => '/login',
                'message' => 'Please login to access this feature.',
            ];
        }

        if (!SubscriptionService::isActive()) {
            return [
                'allowed' => false,
                'redirect' => '/pricing',
                'message' => 'Your subscription is not active. Please renew your plan.',
            ];
        }

        if ($requiredFeature && !SubscriptionService::hasFeature($requiredFeature)) {
            $currentPlan = SubscriptionService::getPlanDisplayName(SubscriptionService::resolveCurrentPlan());

            return [
                'allowed' => false,
                'redirect' => '/dashboard',
                'message' => "This feature requires {$requiredFeature}. Upgrade from {$currentPlan} Plan to unlock this feature.",
            ];
        }

        return [
            'allowed' => true,
            'redirect' => null,
            'message' => null,
        ];
    }

    /**
     * Centralized limit-access decision (usage counter vs current plan cap).
     */
    public static function canUseLimit(string $limitKey, int $currentUsage, int $increment = 0): array
    {
        if (!session('authenticated')) {
            return [
                'allowed' => false,
                'redirect' => '/login',
                'message' => 'Please login to access this feature.',
            ];
        }

        $projectedUsage = max(0, $currentUsage + $increment);
        if (!SubscriptionService::isWithinLimit($limitKey, $projectedUsage)) {
            $currentPlan = SubscriptionService::getPlanDisplayName(SubscriptionService::resolveCurrentPlan());

            return [
                'allowed' => false,
                'redirect' => '/pricing',
                'message' => "Your {$currentPlan} plan has reached the {$limitKey} limit. Upgrade to continue.",
            ];
        }

        return [
            'allowed' => true,
            'redirect' => null,
            'message' => null,
        ];
    }
}
