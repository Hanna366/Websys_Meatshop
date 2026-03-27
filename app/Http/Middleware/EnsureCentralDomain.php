<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower((string) $request->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));

        if (!empty($centralDomains) && !in_array($host, $centralDomains, true)) {
            abort(404);
        }

        return $next($request);
    }
}
