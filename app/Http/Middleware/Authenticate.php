<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $currentContext = $this->currentAuthContext();
        $sessionContext = (string) session('auth_context', '');

        // Require authentication for the active context (central or specific tenant).
        if (!session('authenticated') || $sessionContext !== $currentContext) {
            return redirect('/login');
        }

        if (str_starts_with($currentContext, 'tenant:') && empty(session('user.tenant_id'))) {
            session()->invalidate();
            session()->regenerateToken();

            return redirect('/login')->with('error', 'Please login with a tenant account.');
        }

        $sessionUserId = session('user.id');
        if ($sessionUserId) {
            if (str_starts_with($currentContext, 'tenant:')) {
                $this->ensureTenantConnectionConfigured($request);
            }

            $userQuery = str_starts_with($currentContext, 'tenant:')
                ? User::on('tenant')->newQuery()
                : User::query();

            $freshUser = $userQuery
                ->select(['id', 'name', 'email', 'role', 'tenant_id'])
                ->find($sessionUserId);

            if (!$freshUser) {
                session()->invalidate();
                session()->regenerateToken();

                return redirect('/login');
            }

            session(['user' => [
                'id' => $freshUser->id,
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'role' => $freshUser->role,
                'tenant_id' => $freshUser->tenant_id,
                'plan' => session('user.plan', 'Basic'),
                'features' => session('user.features', []),
            ]]);

            // Align custom session auth with Laravel's guard for authorization middleware.
            Auth::guard('web')->setUser($freshUser);
        }

        $response = $next($request);

        // Prevent browser back-button access to cached protected pages after logout.
        return $response
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    private function currentAuthContext(): string
    {
        $host = strtolower((string) request()->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));

        if ($host !== '' && !in_array($host, $centralDomains, true)) {
            return 'tenant:' . $host;
        }

        return 'central';
    }

    private function ensureTenantConnectionConfigured(Request $request): void
    {
        if (!empty(config('database.connections.tenant'))) {
            return;
        }

        $tenant = (app()->bound('tenant') && tenant()) ? tenant() : $this->resolveTenantFromHost($request);

        if (!$tenant) {
            return;
        }

        app()->instance('tenant', $tenant);
        config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
        DB::purge('tenant');
    }

    private function resolveTenantFromHost(Request $request): ?Tenant
    {
        $host = strtolower((string) $request->getHost());

        if ($host === '') {
            return null;
        }

        if (Schema::hasTable('domains')) {
            $domain = Domain::query()->where('domain', $host)->first();
            if ($domain && $domain->tenant) {
                return $domain->tenant;
            }
        }

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
            return Tenant::query()->where('domain', $host)->first();
        }

        return null;
    }
}
