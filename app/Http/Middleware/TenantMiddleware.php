<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // If this request is for the central app (no tenant domain), skip tenant boot.
        $tenant = null;

        // Tenant resolution should not hard-fail before tenancy migrations are applied.
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
            $tenant = Tenant::where('domain', $host)->first();
        }

        if ($tenant) {
            if (in_array($tenant->status, ['inactive', 'suspended', 'unpaid'], true) || in_array($tenant->payment_status, ['unpaid', 'overdue'], true)) {
                return response()->view('tenant.blocked', [
                    'tenant' => $tenant,
                    'message' => $tenant->suspended_message ?: 'Please contact your administrator.',
                ], 403);
            }

            // Store the tenant for easy access in the app
            app()->instance('tenant', $tenant);

            // If a user is logged in, ensure they belong to this tenant.
            // This prevents a user from using their session for another tenant domain.
            if (session('authenticated') && session('user.tenant_id')) {
                if (session('user.tenant_id') !== $tenant->tenant_id) {
                    session()->invalidate();
                    session()->regenerateToken();

                    return redirect('/login')->with('error', 'Please login for this tenant.');
                }
            }

            // Configure the tenant database connection
            config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);

            // Ensure a fresh tenant connection is used for each request
            DB::purge('tenant');

            // Use tenant connection as the default for this request cycle
            config(['database.default' => 'tenant']);
        }

        return $next($request);
    }
}
