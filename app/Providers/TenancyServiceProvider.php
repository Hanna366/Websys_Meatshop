<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Resolvers\PathTenantResolver;
use Stancl\Tenancy\Resolvers\RequestDataTenantResolver;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
        $this->configureTenantResolverCache();
    }

    protected function mapRoutes(): void
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::group([], base_path('routes/tenant.php'));
            }

            if (file_exists(base_path('routes/tenant/api.php'))) {
                Route::prefix('api')
                    ->middleware([
                        'api',
                        InitializeTenancyByDomain::class,
                        PreventAccessFromCentralDomains::class,
                        'tenant.active',
                    ])
                    ->group(base_path('routes/tenant/api.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        $tenancyMiddleware = [
            PreventAccessFromCentralDomains::class,
            InitializeTenancyByDomain::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }

    protected function configureTenantResolverCache(): void
    {
        $cacheConfig = (array) config('tenancy.tenant_lookup_cache', []);

        $enabled = (bool) ($cacheConfig['enabled'] ?? false);
        $ttl = (int) ($cacheConfig['ttl'] ?? 3600);
        $store = $cacheConfig['store'] ?? null;

        foreach ([DomainTenantResolver::class, PathTenantResolver::class, RequestDataTenantResolver::class] as $resolverClass) {
            $resolverClass::$shouldCache = $enabled;
            $resolverClass::$cacheTTL = $ttl;
            $resolverClass::$cacheStore = $store;
        }
    }
}
