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

        if ($user) {
            $connection = $user->getConnectionName() ?: config('database.default');
            $rolesTable = config('permission.table_names.roles', 'roles');

            if (Schema::connection($connection)->hasTable($rolesTable)) {
                if ($user->hasRole('Owner')) {
                    return $next($request);
                }

                return redirect('/dashboard')->with('error', 'Owner access required.');
            }
        }

        if (strtolower((string) session('user.role', '')) === 'owner') {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Owner access required.');
    }
}
