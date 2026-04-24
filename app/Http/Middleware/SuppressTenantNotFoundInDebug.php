<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Symfony\Component\HttpFoundation\Response;

class SuppressTenantNotFoundInDebug
{
    /**
     * Catch Stancl tenancy domain-not-found exception and allow requests to continue in debug.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TenantCouldNotBeIdentifiedOnDomainException $e) {
            if (config('app.debug')) {
                // swallow and allow the request to continue without tenant context for local dev
                return $next($request);
            }

            throw $e;
        }
    }
}
