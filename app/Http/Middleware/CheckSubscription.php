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
    public function handle(Request $request, Closure $next, $requiredFeature = null)
    {
        $decision = EntitlementService::canAccess($requiredFeature);

        if (!$decision['allowed']) {
            return redirect((string) $decision['redirect'])->with('error', (string) $decision['message']);
        }
        
        return $next($request);
    }
}
