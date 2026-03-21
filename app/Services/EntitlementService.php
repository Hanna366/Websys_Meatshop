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
            $currentPlan = session('user.plan', 'Basic');

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
}
