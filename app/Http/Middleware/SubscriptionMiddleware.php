<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\EntitlementService;

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
        $decision = EntitlementService::canAccess($requiredFeature);

        if (!$decision['allowed']) {
            return redirect((string) $decision['redirect'])->with('error', (string) $decision['message']);
        }

        return $next($request);
    }
}
