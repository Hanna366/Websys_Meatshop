<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('tenant.login');
        }

        // Owner has access to everything
        if ($user->role === 'owner') {
            return $next($request);
        }

        // Manager has access to most features except owner-specific
        if ($user->role === 'manager' && $role !== 'owner') {
            return $next($request);
        }

        // Cashier limited access
        if ($user->role === 'cashier') {
            // Cashier can only access sales and view inventory
            $allowedRoutes = [
                'sales' => ['sales.index', 'sales.create', 'sales.store', 'sales.show'],
                'inventory' => ['inventory.index', 'inventory.show'],
            ];

            $currentRoute = $request->route()->getName();

            // Check if accessing allowed routes
            foreach ($allowedRoutes as $allowedRole => $routes) {
                if (in_array($currentRoute, $routes)) {
                    return $next($request);
                }
            }

            // Deny access to other features
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You do not have permission to access this feature.');
        }

        // Check specific role requirement
        if ($user->role !== $role) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You do not have permission to access this feature.');
        }

        return $next($request);
    }
}
