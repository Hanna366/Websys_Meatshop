<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\EntitlementService;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            $decision = $this->evaluateGuard($request, $guard);

            if (!$decision['allowed']) {
                return redirect((string) $decision['redirect'])->with('error', (string) $decision['message']);
            }
        }

        return $next($request);
    }

    /**
     * Guard formats:
     * - null or "feature_key" => feature gate
     * - "feature:feature_key" => explicit feature gate
     * - "limit:limit_key[:request_input_key][:increment]" => limit gate
     */
    private function evaluateGuard(Request $request, ?string $guard): array
    {
        if ($guard === null || $guard === '') {
            return EntitlementService::canAccess(null);
        }

        if (str_starts_with($guard, 'feature:')) {
            $feature = trim((string) substr($guard, strlen('feature:')));
            return EntitlementService::canAccess($feature);
        }

        if (str_starts_with($guard, 'limit:')) {
            $payload = explode(':', (string) substr($guard, strlen('limit:')));
            $limitKey = (string) ($payload[0] ?? '');
            $requestInputKey = (string) ($payload[1] ?? 'current_usage');
            $increment = (int) ($payload[2] ?? 0);
            $currentUsage = (int) $request->input($requestInputKey, 0);

            return EntitlementService::canUseLimit($limitKey, $currentUsage, $increment);
        }

        return EntitlementService::canAccess($guard);
    }
}
