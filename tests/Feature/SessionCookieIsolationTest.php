<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\ScopeSessionCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SessionCookieIsolationTest extends TestCase
{
    public function test_central_host_gets_central_cookie_name(): void
    {
        config(['tenancy.central_domains' => ['127.0.0.1', 'localhost']]);

        $middleware = new ScopeSessionCookie();
        $request = Request::create('http://localhost/login', 'GET');

        $middleware->handle($request, static fn () => new Response('ok'));

        $base = Str::slug((string) config('app.name', 'laravel'), '_');
        $this->assertSame($base . '_central_session', config('session.cookie'));
        $this->assertNull(config('session.domain'));
    }

    public function test_tenant_host_gets_tenant_cookie_name(): void
    {
        config(['tenancy.central_domains' => ['127.0.0.1', 'localhost']]);

        $middleware = new ScopeSessionCookie();
        $request = Request::create('http://acme.localhost/login', 'GET');

        $middleware->handle($request, static fn () => new Response('ok'));

        $base = Str::slug((string) config('app.name', 'laravel'), '_');
        $this->assertSame($base . '_tenant_session', config('session.cookie'));
        $this->assertNull(config('session.domain'));
    }
}
