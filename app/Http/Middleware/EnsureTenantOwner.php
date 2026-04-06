<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnsureTenantOwner
{
    /**
     * Require Owner role in tenant context.
     * Falls back to session role when permission tables are unavailable.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session('authenticated')) {
            return redirect('/login');
        }

        $user = Auth::guard('web')->user();
        $sessionRole = strtolower((string) session('user.role', ''));
        $sessionIsOwner = in_array($sessionRole, ['owner', 'administrator'], true);

        if ($user) {
            $connection = $user->getConnectionName() ?: config('database.default');
            $rolesTable = config('permission.table_names.roles', 'roles');
            $legacyRole = strtolower((string) $user->role);
            $hasOwnerRole = false;

            if (Schema::connection($connection)->hasTable($rolesTable)) {
                try {
                    $hasOwnerRole = $user->hasAnyRole(['Owner', 'Administrator']);
                } catch (\Throwable $e) {
                    $hasOwnerRole = false;
                }
            }

            if ($hasOwnerRole || in_array($legacyRole, ['owner', 'administrator'], true) || $sessionIsOwner) {
                return $next($request);
            }

            return redirect('/dashboard')->with('error', 'Owner access required.');
        }

        if ($sessionIsOwner) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Owner access required.');
    }
}
