<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InitializeTenancyFromHost
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $host = $request->getHost();
            if (! $host) {
                return $next($request);
            }

            // Try domains table first
            if (\Illuminate\Support\Facades\Schema::hasTable('domains')) {
                $domain = \App\Models\Domain::where('domain', $host)->first();
                if ($domain && $domain->tenant) {
                        try {
                            if (function_exists('tenancy')) {
                                tenancy()->initialize($domain->tenant);
                            } else {
                                app(\Stancl\Tenancy\Tenancy::class)->initialize($domain->tenant);
                            }
                            \Log::info('Initialized tenancy from domain table', ['host' => $host, 'tenant_id' => $domain->tenant->id]);
                            return $next($request);
                        } catch (\Throwable $e) {
                            \Log::warning('tenancy()->initialize threw', ['host' => $host, 'error' => $e->getMessage()]);
                        }
                    }
            }

            // Fallback to tenants.domain
            if (\Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'domain')) {
                $tenant = \App\Models\Tenant::where('domain', $host)->first();
                if ($tenant) {
                    try {
                        if (function_exists('tenancy')) {
                            tenancy()->initialize($tenant);
                        } else {
                            app(\Stancl\Tenancy\Tenancy::class)->initialize($tenant);
                        }
                        \Log::info('Initialized tenancy from tenants.domain', ['host' => $host, 'tenant_id' => $tenant->id]);
                    } catch (\Throwable $e) {
                        \Log::warning('tenancy()->initialize threw for tenant lookup', ['host' => $host, 'error' => $e->getMessage()]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('InitializeTenancyFromHost middleware failed', ['host' => $request->getHost(), 'error' => $e->getMessage()]);
        }

        return $next($request);
    }
}
