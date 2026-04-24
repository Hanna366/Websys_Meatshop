<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Stancl\Tenancy\Database\Models\Tenant as TenancyTenant;

class InitializeTenancyByQuery
{
    /**
     * Initialize tenancy when a `tenant` query parameter or `X-Tenant-ID` header is present.
     * This is a dev helper to allow loading tenant pages on localhost without DNS mapping.
     */
    public function handle(Request $request, Closure $next)
    {
        if (function_exists('tenant') && tenant()) {
            return $next($request);
        }

        $tenantKey = $request->query('tenant') ?: $request->header('X-Tenant-ID');
        if (! $tenantKey) {
            return $next($request);
        }

        try {
            $tenant = TenancyTenant::where('id', $tenantKey)
                ->orWhere('tenant_id', $tenantKey)
                ->first();

            if ($tenant) {
                /** @var Tenancy $tenancy */
                $tenancy = app(Tenancy::class);
                $tenancy->initialize($tenant);
            }
        } catch (\Throwable $e) {
            // don't block the request if tenancy initialization fails; dev helper only
        }

        return $next($request);
    }
}
