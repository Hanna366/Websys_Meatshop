<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        if (session('authenticated') && session('auth_context') === $this->currentAuthContext()) {
            return redirect('/dashboard');
        }

        return $next($request);
    }

    private function currentAuthContext(): string
    {
        if (app()->bound('tenant') && tenant()) {
            return 'tenant:' . tenant()->tenant_id;
        }

        return 'central';
    }
}
