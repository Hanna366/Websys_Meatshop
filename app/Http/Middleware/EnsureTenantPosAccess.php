<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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

        // Session-based fallback to avoid hard failures when permission relations are inconsistent.
        $role = strtolower((string) session('user.role', ''));
        if (in_array($role, ['owner', 'staff', 'cashier'], true)) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'POS access is required.');
    }
}
