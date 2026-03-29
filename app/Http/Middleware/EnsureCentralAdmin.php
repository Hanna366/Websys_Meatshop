<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCentralAdmin
{
    /**
     * Allow only central-admin users to access central tenant management actions.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $role = strtolower((string) ($user->role ?? ''));
        $isCentralUser = empty($user->tenant_id);
        $isPrivilegedRole = in_array($role, ['owner', 'admin', 'administrator', 'super_admin', 'superadmin'], true);

        if (!$isCentralUser || !$isPrivilegedRole) {
            abort(403, 'Only central administrators can manage tenants.');
        }

        return $next($request);
    }
}