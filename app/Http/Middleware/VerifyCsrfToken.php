<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        'login',
        'logout',
        // Allow tenant-origin public subscription requests without CSRF
        // so unauthenticated tenant UIs can submit manual requests.
        '/subscription/request-public',
        // Allow tenant issue reports without CSRF
        '/dashboard/updates/report',
    ];
}
