<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ScopeSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower((string) $request->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));
        $isCentralHost = in_array($host, $centralDomains, true);

        $base = Str::slug((string) config('app.name', 'laravel'), '_');
        $cookieName = $base . '_' . ($isCentralHost ? 'central' : 'tenant') . '_session';

        config([
            'session.cookie' => $cookieName,
            // Force host-only cookies to prevent cross-domain session bleed.
            'session.domain' => null,
        ]);

        return $next($request);
    }
}
