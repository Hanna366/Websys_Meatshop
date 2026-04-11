<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveTenant
{
    /**
     * Block tenant hosts that are disabled or unpaid.
     */
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        $subscription = is_array($tenant->subscription) ? $tenant->subscription : [];
        $subscriptionStatus = strtolower((string) ($subscription['status'] ?? 'active'));
        $periodEnd = $subscription['current_period_end'] ?? optional($tenant->plan_ends_at)->toDateString();
        $isExpiredByDate = false;

        if ($periodEnd) {
            $isExpiredByDate = now()->startOfDay()->gt(\Carbon\Carbon::parse((string) $periodEnd)->endOfDay());
        }

        if (in_array($tenant->status, ['inactive', 'suspended', 'unpaid'], true)
            || in_array($tenant->payment_status, ['unpaid', 'overdue'], true)
            || in_array($subscriptionStatus, ['expired', 'unpaid', 'cancelled'], true)
            || $isExpiredByDate) {
            return response()->view('tenant.blocked', [
                'tenant' => $tenant,
                'message' => $tenant->disabled_message ?? $tenant->suspended_message ?? 'Tenant access is currently unavailable. Please contact your administrator.',
            ], 403);
        }

        return $next($request);
    }
}
