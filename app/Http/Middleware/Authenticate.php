<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

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
        $sessionContext = (string) session('auth_context', '');

        // Require authentication for the active context (central or specific tenant).
        if (!session('authenticated') || $sessionContext !== $this->currentAuthContext()) {
            return redirect('/login');
        }

        $sessionUserId = session('user.id');
        if ($sessionUserId) {
            $freshUser = User::query()->select(['id', 'name', 'email', 'role', 'tenant_id'])->find($sessionUserId);

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
        if (app()->bound('tenant') && tenant()) {
            return 'tenant:' . tenant()->tenant_id;
        }

        return 'central';
    }
}
