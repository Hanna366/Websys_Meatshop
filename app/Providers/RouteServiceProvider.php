<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id);
        });

        $centralApiRoutes = file_exists(base_path('routes/central/api.php'))
            ? base_path('routes/central/api.php')
            : base_path('routes/api.php');

        $centralDomains = config('tenancy.central_domains', ['127.0.0.1', 'localhost']);

        if (empty($centralDomains)) {
            Route::prefix('api')
                ->middleware('api')
                ->group($centralApiRoutes);

            Route::middleware('web')->group(base_path('routes/web.php'));
            return;
        }

        foreach ($centralDomains as $domain) {
            Route::prefix('api')
                ->middleware('api')
                ->domain($domain)
                ->group($centralApiRoutes);

            Route::middleware('web')
                ->domain($domain)
                ->group(base_path('routes/web.php'));
        }
    }
}
