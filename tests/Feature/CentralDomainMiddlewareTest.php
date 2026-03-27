<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureCentralDomain;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CentralDomainMiddlewareTest extends TestCase
{
    public function test_central_domain_allows_request(): void
    {
        config(['tenancy.central_domains' => ['127.0.0.1', 'localhost']]);

        $middleware = new EnsureCentralDomain();
        $request = Request::create('http://localhost/test', 'GET');

        $response = $middleware->handle($request, static fn () => new Response('ok'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_non_central_domain_is_blocked(): void
    {
        config(['tenancy.central_domains' => ['127.0.0.1', 'localhost']]);

        $middleware = new EnsureCentralDomain();
        $request = Request::create('http://evil.example/test', 'GET');

        try {
            $middleware->handle($request, static fn () => new Response('ok'));
            $this->fail('Expected middleware to abort non-central domains with 404.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
