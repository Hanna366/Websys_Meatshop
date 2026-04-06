<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnsureTenantPosAccess
{
    /**
     * Require POS access in tenant context.
     * Uses Spatie permission when available, then falls back to session role.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session('authenticated')) {
            return redirect('/login');
        }

        $user = Auth::guard('web')->user();
        if ($user) {
            $connection = $user->getConnectionName() ?: config('database.default');
            $rolesTable = config('permission.table_names.roles', 'roles');

            if (Schema::connection($connection)->hasTable($rolesTable)) {
                try {
                    if ($user->hasAnyRole(['Owner', 'Administrator', 'Cashier'])) {
                        return $next($request);
                    }
                } catch (\Throwable $e) {
                    // Fall through to legacy/session role checks.
                }
            }

            $legacyRole = strtolower((string) $user->role);
            if (in_array($legacyRole, ['owner', 'administrator', 'cashier', 'inventory_staff'], true)) {
                return $next($request);
            }
        }

        // Session-based fallback to avoid hard failures when permission relations are inconsistent.
        $role = strtolower((string) session('user.role', ''));
        if (in_array($role, ['owner', 'administrator', 'cashier', 'inventory_staff'], true)) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'POS access is required.');
    }
}
