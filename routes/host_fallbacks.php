<?php

use Illuminate\Http\Request;

// Lightweight host-based fallback routes that try to resolve a tenant by
// the request host and initialize tenancy before forwarding to tenant
// controllers. These are registered globally (no domain constraint) so
// tenant hosts like "chop.localhost" are handled even when main
// `routes/web.php` is registered under central domains.

Route::get('/payments/checkout', function (Request $request) {
    $host = $request->getHost();

    $domain = \App\Models\Domain::where('domain', $host)->first();
    $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();

    if (! $tenant) {
        return abort(404);
    }

    tenancy()->initialize($tenant);

    $controller = app()->make(App\Http\Controllers\Tenant\PaymentsController::class);
    return $controller->checkout($request);
});

Route::get('/dashboard/payments/checkout', function (Request $request) {
    $host = $request->getHost();

    $domain = \App\Models\Domain::where('domain', $host)->first();
    $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();

    if (! $tenant) {
        return abort(404);
    }

    tenancy()->initialize($tenant);

    $controller = app()->make(App\Http\Controllers\Tenant\PaymentsController::class);
    return $controller->checkout($request);
});

Route::get('/checkout', function () {
    $qs = request()->getQueryString();
    return redirect('/dashboard/payments/checkout' . ($qs ? '?' . $qs : ''));
});

// Test public route (no auth required)
Route::get('/test-report-route', function () {
    return 'Report route is accessible without auth!';
});

// Public report endpoint - no auth required
Route::post('/dashboard/updates/report', function (Request $request) {
    \Log::info('HOST_FALLBACKS: Report route reached', ['host' => $request->getHost(), 'input' => $request->all()]);
    
    $host = $request->getHost();

    $domain = \App\Models\Domain::where('domain', $host)->first();
    $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();

    if (! $tenant) {
        \Log::error('HOST_FALLBACKS: No tenant found for host', ['host' => $host]);
        return abort(404);
    }

    \Log::info('HOST_FALLBACKS: Initializing tenancy', ['tenant_id' => $tenant->id]);
    tenancy()->initialize($tenant);

    $controller = app()->make(App\Http\Controllers\TenantUpdateController::class);
    return $controller->report($request);
});
