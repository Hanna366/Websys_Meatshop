<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureCentralDomain;
use App\Http\Middleware\ScopeSessionCookie;
use App\Tenancy\Bootstrappers\LegacyDatabaseBootstrapper;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;
use Tests\TestCase;

class TenancyHardeningConfigTest extends TestCase
{
    public function test_tenancy_bootstrappers_include_cache_filesystem_and_queue(): void
    {
        $bootstrappers = (array) config('tenancy.bootstrappers', []);

        $this->assertContains(LegacyDatabaseBootstrapper::class, $bootstrappers);
        $this->assertContains(CacheTenancyBootstrapper::class, $bootstrappers);
        $this->assertContains(FilesystemTenancyBootstrapper::class, $bootstrappers);
        $this->assertContains(QueueTenancyBootstrapper::class, $bootstrappers);
    }

    public function test_tenant_lookup_cache_is_enabled_with_ttl(): void
    {
        $config = (array) config('tenancy.tenant_lookup_cache', []);

        $this->assertTrue((bool) ($config['enabled'] ?? false));
        $this->assertGreaterThan(0, (int) ($config['ttl'] ?? 0));
    }

    public function test_central_route_uses_central_domain_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('central.home');

        $this->assertNotNull($route);
        $this->assertContains('central.domain', $route->middleware());
    }

    public function test_web_group_contains_session_cookie_scoping_middleware(): void
    {
        $groups = app('router')->getMiddlewareGroups();

        $this->assertContains(ScopeSessionCookie::class, $groups['web'] ?? []);
    }

    public function test_central_domain_alias_points_to_expected_middleware(): void
    {
        $aliases = app('router')->getMiddleware();

        $this->assertSame(EnsureCentralDomain::class, $aliases['central.domain'] ?? null);
    }
}
