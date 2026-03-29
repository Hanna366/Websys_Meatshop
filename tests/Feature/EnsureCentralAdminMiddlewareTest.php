<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureCentralAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureCentralAdminMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Auth::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_central_owner_is_allowed(): void
    {
        $user = new User([
            'tenant_id' => null,
            'role' => 'owner',
        ]);

        Auth::shouldReceive('guard')->once()->with('web')->andReturnSelf();
        Auth::shouldReceive('user')->once()->andReturn($user);

        $middleware = new EnsureCentralAdmin();
        $request = Request::create('http://localhost/tenants', 'GET');

        $response = $middleware->handle($request, static fn () => new Response('ok'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_tenant_scoped_user_is_blocked(): void
    {
        $user = new User([
            'tenant_id' => 'tenant-123',
            'role' => 'owner',
        ]);

        Auth::shouldReceive('guard')->once()->with('web')->andReturnSelf();
        Auth::shouldReceive('user')->once()->andReturn($user);

        $middleware = new EnsureCentralAdmin();
        $request = Request::create('http://localhost/tenants', 'GET');

        try {
            $middleware->handle($request, static fn () => new Response('ok'));
            $this->fail('Expected tenant-scoped users to be blocked with 403.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_non_privileged_central_user_is_blocked(): void
    {
        $user = new User([
            'tenant_id' => null,
            'role' => 'cashier',
        ]);

        Auth::shouldReceive('guard')->once()->with('web')->andReturnSelf();
        Auth::shouldReceive('user')->once()->andReturn($user);

        $middleware = new EnsureCentralAdmin();
        $request = Request::create('http://localhost/tenants', 'GET');

        try {
            $middleware->handle($request, static fn () => new Response('ok'));
            $this->fail('Expected non-privileged central users to be blocked with 403.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
