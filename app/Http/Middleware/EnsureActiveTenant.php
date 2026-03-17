<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveTenant
{
    /**
     * Block tenant hosts that are disabled or unpaid.
     */
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        if (in_array($tenant->status, ['inactive', 'suspended', 'unpaid'], true)
            || in_array($tenant->payment_status, ['unpaid', 'overdue'], true)) {
            return response()->view('tenant.blocked', [
                'tenant' => $tenant,
                'message' => $tenant->suspended_message ?: 'Please contact your administrator.',
            ], 403);
        }

        return $next($request);
    }
}
