<?php

// Version / Update Management (handled below within PHP tags)

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\InitializeTenancyByQuery;
use App\Http\Middleware\SuppressTenantNotFoundInDebug;
use App\Http\Controllers\CentralDashboardController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\UpdateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Version Management Routes (Admin only)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'central.admin'])->group(function () {
    Route::get('/versions', [VersionController::class, 'index'])->name('versions.index');
    Route::get('/versions/create', [VersionController::class, 'create'])->name('versions.create');
    Route::post('/versions', [VersionController::class, 'store'])->name('versions.store');
    Route::get('/versions/{version}', [VersionController::class, 'show'])->name('versions.show');
    Route::get('/versions/{version}/edit', [VersionController::class, 'edit'])->name('versions.edit');
    Route::put('/versions/{version}', [VersionController::class, 'update'])->name('versions.update');
    Route::delete('/versions/{version}', [VersionController::class, 'destroy'])->name('versions.destroy');
    
    // AJAX endpoints
    Route::get('/versions/check-updates', [VersionController::class, 'checkUpdates'])->name('versions.check-updates');
    Route::post('/versions/download', [VersionController::class, 'downloadUpdate'])->name('versions.download');
    Route::post('/versions/install', [VersionController::class, 'installUpdate'])->name('versions.install');
    Route::post('/versions/upload', [VersionController::class, 'uploadPackage'])->name('versions.upload');
    Route::post('/versions/simulate', [VersionController::class, 'simulateUpdate'])->name('versions.simulate');
    Route::get('/versions/update-files', [VersionController::class, 'listUpdateFiles'])->name('versions.update-files');
    Route::get('/versions/status', [VersionController::class, 'getUpdateStatus'])->name('versions.status');
    
    // GitHub integration endpoints
    Route::post('/versions/sync-github', [VersionController::class, 'syncGitHub'])->name('versions.sync-github');
    Route::post('/versions/clear-github-cache', [VersionController::class, 'clearGitHubCache'])->name('versions.clear-github-cache');
    Route::get('/versions/github-releases', [VersionController::class, 'getGitHubReleases'])->name('versions.github-releases');
});

// Central subscription approval UI
use App\Http\Controllers\CentralSubscriptionController;
Route::prefix('admin')->name('admin.')->middleware(['auth', 'central.admin'])->group(function () {
    Route::get('/subscription-requests', [CentralSubscriptionController::class, 'index'])->name('subscription_requests.index');
    Route::get('/subscription-requests/updates', [CentralSubscriptionController::class, 'updates'])->name('subscription_requests.updates');
    Route::post('/subscription-requests/{id}/approve', [CentralSubscriptionController::class, 'approve'])->name('subscription_requests.approve');
    Route::post('/subscription-requests/{id}/reject', [CentralSubscriptionController::class, 'reject'])->name('subscription_requests.reject');
});

// Backwards-compatible redirect for legacy/non-prefixed URL
Route::get('/subscription-requests', function () {
    return redirect('/admin/subscription-requests');
});

Route::get('/', [CentralDashboardController::class, 'welcome'])->name('central.welcome');
Route::get('/central', [CentralDashboardController::class, 'index'])->name('central.home');

Route::get('/login', [SimpleAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SimpleAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [SimpleAuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])->name('password.reset.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.reset.send');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset.update');

// Public signup to create tenant from selected plan.
Route::get('/account/create', [TenantController::class, 'create'])->name('tenants.create');
Route::post('/account/create', [TenantController::class, 'store'])->middleware('throttle:10,1')->name('tenants.store');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CentralDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'central.admin'])->group(function () {
    // Central tenant management menu entries.
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenant/{tenantId}', [TenantController::class, 'show'])->name('tenants.show');

// Dev-only debug endpoint to inspect subscription/session state
if (config('app.debug')) {
    Route::get('/_debug/subscription', [App\Http\Controllers\DevDebugController::class, 'subscription'])->name('debug.subscription');
}

// Dev-only: list tenants with quick links to open tenant pages via ?tenant=UUID
if (config('app.debug')) {
    Route::get('/_debug/tenants', function () {
        $tenants = \Stancl\Tenancy\Database\Models\Tenant::all();
        $items = $tenants->map(function ($t) {
            $display = $t->name ?? $t->domain ?? $t->tenant_id ?? $t->id;
            $uuid = $t->tenant_id ?? $t->id;
            $url = url('/dashboard/updates') . '?tenant=' . $uuid;
            return "<li><a href=\"{$url}\">{htmlspecialchars($display)} — {$uuid}</a></li>";
        })->implode('');

        return response("<h1>Tenants</h1><ul>{$items}</ul>", 200)->header('Content-Type', 'text/html');
    });
}

// Dev-only: check current tenant initialization (no auth)
if (config('app.debug')) {
    Route::get('/_debug/tenant', function () {
        $tenant = function_exists('tenant') ? tenant() : null;
        if (! $tenant) {
            return response()->json(['tenant' => null, 'message' => 'No tenant initialized']);
        }

        return response()->json([
            'tenant_id' => $tenant->tenant_id ?? $tenant->id ?? null,
            'business_name' => $tenant->business_name ?? null,
            'domain' => $tenant->domain ?? null,
        ]);
    });
}

// Dev-only: lightweight tenant viewer (no auth) to help open tenant pages on localhost
if (config('app.debug')) {
    Route::get('/_dev/tenant-view/{id}', function ($id) {
        $t = \Stancl\Tenancy\Database\Models\Tenant::where('tenant_id', $id)
            ->orWhere('id', $id)
            ->first();

        if (! $t) {
            return response('Tenant not found', 404);
        }

        $uuid = $t->tenant_id ?? $t->id;
        $link = url('/dashboard/updates') . '?tenant=' . $uuid;

        return "<h1>Tenant: " . htmlspecialchars($t->business_name ?? $uuid) . "</h1><p>ID: " . htmlspecialchars($uuid) . "</p><p><a href='" . htmlspecialchars($link) . "'>Open Updates (with ?tenant)</a></p>";
    });
}

// Dev-only: render real tenant updates page without auth/tenancy (preview)
if (config('app.debug')) {
    Route::get('/_dev/preview/{id}', [App\Http\Controllers\DevTenantPreviewController::class, 'preview']);
}
    Route::post('/tenant/{tenantId}', [TenantController::class, 'update'])->name('tenants.update');
    Route::post('/tenant/{tenantId}/status', [TenantController::class, 'updateStatus'])->name('tenants.updateStatus');
    Route::post('/tenant/{tenantId}/subscription', [TenantController::class, 'updateSubscription'])->name('tenants.updateSubscription');
});

// Central admin: view tenant support tickets / update requests
Route::prefix('admin')->name('admin.')->middleware(['auth', 'central.admin'])->group(function () {
    Route::get('/support-tickets', [App\Http\Controllers\CentralSupportTicketsController::class, 'index'])->name('support_tickets.index');
    Route::get('/support-tickets/{id}', [App\Http\Controllers\CentralSupportTicketsController::class, 'show'])->name('support_tickets.show');
    Route::post('/support-tickets/{id}/status', [App\Http\Controllers\CentralSupportTicketsController::class, 'updateStatus'])->name('support_tickets.update_status');
    // Update requests management
    Route::get('/update-requests', [App\Http\Controllers\CentralUpdateRequestsController::class, 'index'])->name('update_requests.index');
    Route::get('/update-requests/{id}', [App\Http\Controllers\CentralUpdateRequestsController::class, 'show'])->name('update_requests.show');
    Route::post('/update-requests/{id}/status', [App\Http\Controllers\CentralUpdateRequestsController::class, 'updateStatus'])->name('update_requests.update_status');
});

// Subscription routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/pricing', [SubscriptionController::class, 'index'])->name('pricing');
    Route::post('/subscription/process', [SubscriptionController::class, 'processSubscription'])->name('subscription.process');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/renew', [SubscriptionController::class, 'renew'])->name('subscription.renew');
    Route::get('/subscription/status', [SubscriptionController::class, 'status'])->name('subscription.status');
    Route::get('/subscription/billing', [SubscriptionController::class, 'billingPage'])->name('subscription.billing');
    Route::get('/subscription/billing/data', [SubscriptionController::class, 'billingData'])->name('subscription.billing.data');
    Route::put('/subscription/settings', [SubscriptionController::class, 'updateSettings'])->name('subscription.settings');
});

Route::get('/test', function () {
    return 'Laravel Meat Shop POS is working!';
});

// GitHub webhook for instant release notifications
Route::post('/webhook/github', [App\Http\Controllers\WebhookController::class, 'github'])->name('webhook.github');

// Dev-only quick sync endpoint (runs cache clear + sync)
if (config('app.debug')) {
    Route::get('/_debug/sync-github', function (\Illuminate\Http\Request $request) {
        try {
            \App\Services\GitHubService::clearCache();
            $result = \App\Services\VersionManagementService::syncGitHubReleases();
            return response()->json(['success' => true, 'message' => 'Sync completed', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    })->name('debug.sync_github');
}



// Dev-only user inspection route (debug only)
if (config('app.debug')) {
    Route::get('/_debug/user-check', function (\Illuminate\Http\Request $request) {
        $email = (string) $request->query('email', '');
        $host = strtolower((string) $request->query('domain', $request->getHost()));
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));
        $tenant = null;

        if ($host !== '' && !in_array($host, $centralDomains, true)) {
            if (\Illuminate\Support\Facades\Schema::hasTable('domains')) {
                $domain = \App\Models\Domain::where('domain', $host)->first();
                if ($domain && $domain->tenant) {
                    $tenant = $domain->tenant;
                }
            }

            if (!$tenant && \Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'domain')) {
                $tenant = \App\Models\Tenant::where('domain', $host)->first();
            }
        }

        $defaultUser = \App\Models\User::where('email', $email)->first();

        $tenantUser = null;
        if ($tenant) {
            config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
            \Illuminate\Support\Facades\DB::purge('tenant');
            $tenantUser = \App\Models\User::on('tenant')->where('email', $email)->first();
        }

        return response()->json([
            'host' => $host,
            'tenant' => $tenant ? [
                'tenant_id' => $tenant->tenant_id ?? null,
                'domain' => $tenant->domain ?? null
            ] : null,
            'default_user' => $defaultUser ? [
                'id' => $defaultUser->id,
                'connection' => $defaultUser->getConnectionName(),
                'tenant_id' => $defaultUser->tenant_id,
                'password_preview' => substr($defaultUser->password, 0, 60),
                'updated_at' => (string) $defaultUser->updated_at,
            ] : null,
            'tenant_user' => $tenantUser ? [
                'id' => $tenantUser->id,
                'connection' => $tenantUser->getConnectionName(),
                'tenant_id' => $tenantUser->tenant_id,
                'password_preview' => substr($tenantUser->password, 0, 60),
                'updated_at' => (string) $tenantUser->updated_at,
            ] : null,
        ]);
    })->name('debug.user_check');

    Route::post('/_debug/set-tenant-password', function (\Illuminate\Http\Request $request) {
        $email = (string) $request->input('email', '');
        $domain = (string) $request->input('domain', $request->getHost());

        $tenant = null;
        if ($domain !== '') {
            if (\Illuminate\Support\Facades\Schema::hasTable('domains')) {
                $d = \App\Models\Domain::where('domain', $domain)->first();
                if ($d && $d->tenant) {
                    $tenant = $d->tenant;
                }
            }

            if (!$tenant && \Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'domain')) {
                $tenant = \App\Models\Tenant::where('domain', $domain)->first();
            }
        }

        if (!$tenant) {
            return response()->json(['success' => false, 'error' => 'Tenant not found for domain: '.$domain], 404);
        }

        config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
        \Illuminate\Support\Facades\DB::purge('tenant');

        $user = \App\Models\User::on('tenant')->where('email', $email)->first();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'User not found in tenant DB: '.$email], 404);
        }

        $password = (string) $request->input('password', '');
        if ($password === '') {
            $password = substr(bin2hex(random_bytes(8)), 0, 12);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($password);
        $user->save();

        return response()->json(['success' => true, 'email' => $email, 'password' => $password]);
    })->name('debug.set_tenant_password');

    // GET version for quick dev use (avoids CSRF token during local debugging)
    Route::get('/_debug/set-tenant-password', function (\Illuminate\Http\Request $request) {
        $email = (string) $request->query('email', '');
        $domain = (string) $request->query('domain', $request->getHost());

        $tenant = null;
        if ($domain !== '') {
            if (\Illuminate\Support\Facades\Schema::hasTable('domains')) {
                $d = \App\Models\Domain::where('domain', $domain)->first();
                if ($d && $d->tenant) {
                    $tenant = $d->tenant;
                }
            }

            if (!$tenant && \Illuminate\Support\Facades\Schema::hasTable('tenants') && \Illuminate\Support\Facades\Schema::hasColumn('tenants', 'domain')) {
                $tenant = \App\Models\Tenant::where('domain', $domain)->first();
            }
        }

        if (!$tenant) {
            return response()->json(['success' => false, 'error' => 'Tenant not found for domain: '.$domain], 404);
        }

        config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
        \Illuminate\Support\Facades\DB::purge('tenant');

        $user = \App\Models\User::on('tenant')->where('email', $email)->first();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'User not found in tenant DB: '.$email], 404);
        }

        $password = (string) $request->query('password', '');
        if ($password === '') {
            $password = substr(bin2hex(random_bytes(8)), 0, 12);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($password);
        $user->save();

        return response()->json(['success' => true, 'email' => $email, 'password' => $password]);
    });
}

// Logo testing routes
Route::get('/logo/test', [App\Http\Controllers\LogoController::class, 'testLogos']);
Route::get('/logo/generate/{tenantId?}', [App\Http\Controllers\LogoController::class, 'generateLogo']);

// System update UI (admin only)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'central.admin'])->group(function () {
    // Admin payments review
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentsController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [App\Http\Controllers\Admin\PaymentsController::class, 'show'])->name('payments.show');
    Route::post('/payments/{id}/approve', [App\Http\Controllers\Admin\PaymentsController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{id}/reject', [App\Http\Controllers\Admin\PaymentsController::class, 'reject'])->name('payments.reject');

    Route::get('/update', [UpdateController::class, 'index'])->name('update.index');
    Route::post('/update', [UpdateController::class, 'update'])->name('update.perform');
    Route::get('/update/status', [UpdateController::class, 'status'])->name('update.status');
    Route::post('/update/sync', [UpdateController::class, 'sync'])->name('update.sync');
});

// System update API endpoints (admin only)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'central.admin'])->group(function () {
    Route::get('/updates/releases', [App\Http\Controllers\SystemUpdateController::class, 'listReleases'])->name('updates.releases');
    Route::post('/updates/download-latest', [App\Http\Controllers\SystemUpdateController::class, 'downloadLatest'])->name('updates.download-latest');
    Route::get('/updates/status', [App\Http\Controllers\SystemUpdateController::class, 'status'])->name('updates.status');
});

// Top-level handler for legacy '/payments/checkout' — initialize tenancy
// by host and dispatch to the tenant PaymentsController so requests
// to '/payments/checkout' resolve on tenant subdomains.
Route::get('/payments/checkout', function (\Illuminate\Http\Request $request) {
    $host = $request->getHost();

    $domain = \App\Models\Domain::where('domain', $host)->first();
    $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();

    if (! $tenant) {
        return abort(404);
    }

    tenancy()->initialize($tenant);

    return app()->call([App\Http\Controllers\Tenant\PaymentsController::class, 'checkout']);
});

// Also accept legacy '/checkout' (some clients may navigate relatively)
Route::get('/checkout', function () {
    $qs = request()->getQueryString();
    return redirect('/dashboard/payments/checkout' . ($qs ? '?' . $qs : ''));
});

// Top-level handler for tenant dashboard checkout: try to initialize
// tenancy by host and dispatch to the tenant controller. This ensures
// requests to tenant subdomains (e.g. chop.localhost) resolve even when
// route host bindings are not present.
Route::get('/dashboard/payments/checkout', function (\Illuminate\Http\Request $request) {
    $host = $request->getHost();

    $domain = \App\Models\Domain::where('domain', $host)->first();
    $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();

    if (! $tenant) {
        return abort(404);
    }

    tenancy()->initialize($tenant);

    // Forward the request to the tenant PaymentsController.checkout action.
    return app()->call([App\Http\Controllers\Tenant\PaymentsController::class, 'checkout']);
});

// Allow unauthenticated tenants to submit manual payment proofs without
// being forced to log in. This initializes tenancy by host then forwards
// the POST to the tenant PaymentsController@store action. Placed before
// the auth-protected tenant group so guests aren't redirected to login.
Route::post('/dashboard/payments/checkout', function (\Illuminate\Http\Request $request) {
    $host = $request->getHost();

    $domain = \App\Models\Domain::where('domain', $host)->first();
    $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();

    if (! $tenant) {
        return abort(404);
    }

    tenancy()->initialize($tenant);

    return app()->call([App\Http\Controllers\Tenant\PaymentsController::class, 'store']);
});

// Public subscription request endpoint for tenant origins.
// This accepts manual subscription requests (no auth) and records them
// to the central `subscription_requests` table after initializing
// tenancy by host. Used by tenant UIs to submit requests without
// forcing a login redirect (UI should still ensure the user is a
// legitimate tenant owner when possible).
Route::post('/subscription/request-public', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('subscription.request-public route called', ['host' => $request->getHost(), 'payload' => $request->all()]);
    // Resolve tenant by request body (tenant_host or tenant_id) first,
    // then fall back to Host header. This allows central-rendered pages
    // to POST a tenant_host and have the request recorded centrally.
    $tenant = null;

    if ($request->filled('tenant_id')) {
        $tenant = \App\Models\Tenant::where('tenant_id', $request->input('tenant_id'))->first();
    }

    if (! $tenant && $request->filled('tenant_host')) {
        $tenantHost = $request->input('tenant_host');
        $domain = \App\Models\Domain::where('domain', $tenantHost)->first();
        $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $tenantHost)->first();
    }

    if (! $tenant) {
        // Fall back to Host header behavior for tenant-origin requests
        $host = $request->getHost();
        $domain = \App\Models\Domain::where('domain', $host)->first();
        $tenant = $domain ? $domain->tenant : \App\Models\Tenant::where('domain', $host)->first();
    }

    if (! $tenant) {
        return response()->json(['error' => 'Not in tenant context'], 400);
    }

    if (function_exists('tenancy')) {
        tenancy()->initialize($tenant);
    } else {
        app(\Stancl\Tenancy\Tenancy::class)->initialize($tenant);
    }

    return app()->call([App\Http\Controllers\SubscriptionController::class, 'requestSubscriptionPublic']);
})->withoutMiddleware([\App\Http\Middleware\EnsureCentralDomain::class]);

// Public tenant report endpoint (must be defined BEFORE auth group to take precedence)
Route::middleware(['web'])->post('/dashboard/updates/report', [App\Http\Controllers\TenantUpdateController::class, 'report'])
    ->name('tenant.updates.report.public')
    ->withoutMiddleware([\App\Http\Middleware\EnsureCentralDomain::class]);

// Tenant-facing System Updates UI (read-only + reporting)
$tenantMiddleware = ['auth', InitializeTenancyByQuery::class, InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class, 'tenant.active'];
// In non-debug (production-like) environments, wrap domain initialization to
// catch tenant-not-found gracefully by adding the suppress middleware.
if (! config('app.debug')) {
    array_unshift($tenantMiddleware, SuppressTenantNotFoundInDebug::class);
}

Route::middleware($tenantMiddleware)->group(function () {
    // Backwards-compatible route: some client code historically
    // navigated to `/payments/checkout`. Provide a redirect so that
    // requests reach the tenant-scoped checkout at
    // `/dashboard/payments/checkout` and keep query parameters.
    Route::get('/payments/checkout', function () {
        $qs = request()->getQueryString();
        return redirect('/dashboard/payments/checkout' . ($qs ? '?' . $qs : ''));
    });
    // Tenant manual payments
    Route::get('/dashboard/payments/checkout', [App\Http\Controllers\Tenant\PaymentsController::class, 'checkout'])->name('tenant.payments.checkout');
    // Note: POST is handled by a public route earlier so guests submitting
    // manual payment proofs are not redirected to login. Do not re-register
    // a POST route here that includes the 'auth' middleware.
    Route::get('/dashboard/payments/history', [App\Http\Controllers\Tenant\PaymentsController::class, 'history'])->name('tenant.payments.history');

    // Tenant System Updates routes are now in routes/tenant.php

    // Tenant support routes
    Route::get('/dashboard/support', [App\Http\Controllers\SupportTicketController::class, 'index'])->name('tenant.support.index');
    Route::post('/dashboard/support', [App\Http\Controllers\SupportTicketController::class, 'store'])->name('tenant.support.store');
});

// Admin: tenant settings editor
Route::prefix('admin')->name('admin.')->middleware(['auth', 'central.admin'])->group(function () {
    Route::get('/tenants/{tenantId}/settings', [App\Http\Controllers\Admin\TenantSettingsController::class, 'edit'])->name('tenants.settings.edit');
    Route::post('/tenants/{tenantId}/settings', [App\Http\Controllers\Admin\TenantSettingsController::class, 'update'])->name('tenants.settings.update');
});

// Simple subscription request that works
Route::post('/simple-subscription-request', function (\Illuminate\Http\Request $request) {
    try {
        $plan = $request->input('plan', 'basic');
        $tenant = \App\Models\Tenant::first();
        
        if (!$tenant) {
            return response()->json(['error' => 'No tenant found'], 400);
        }
        
        // Use correct pricing
        $pesoRate = env('PESO_RATE', 55);
        $pricing = [
            'basic' => 29 * $pesoRate,     // ₱1,595
            'standard' => 79 * $pesoRate,  // ₱4,345  
            'premium' => 149 * $pesoRate,  // ₱8,195
        ];
        
        $subscriptionRequest = new \App\Models\SubscriptionRequest();
        $subscriptionRequest->tenant_id = $tenant->tenant_id;
        $subscriptionRequest->requested_plan = $plan;
        $subscriptionRequest->payment_method = null;
        $subscriptionRequest->payment_reference = null;
        $subscriptionRequest->amount = (float) ($pricing[$plan] ?? 0);
        $subscriptionRequest->status = 'pending';
        $subscriptionRequest->metadata = json_encode(['source' => 'simple_form']);
        $subscriptionRequest->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription request created! ID: ' . $subscriptionRequest->id,
            'request_id' => $subscriptionRequest->id
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// GET endpoint to create subscription request (no CSRF)
Route::get('/create-subscription/{plan}', function ($plan) {
    try {
        $tenant = \App\Models\Tenant::first();
        
        if (!$tenant) {
            return 'No tenant found';
        }
        
        // Use correct pricing
        $pesoRate = env('PESO_RATE', 55);
        $pricing = [
            'basic' => 29 * $pesoRate,     // ₱1,595
            'standard' => 79 * $pesoRate,  // ₱4,345  
            'premium' => 149 * $pesoRate,  // ₱8,195
        ];
        
        $subscriptionRequest = new \App\Models\SubscriptionRequest();
        $subscriptionRequest->tenant_id = $tenant->tenant_id;
        $subscriptionRequest->requested_plan = $plan;
        $subscriptionRequest->payment_method = null;
        $subscriptionRequest->payment_reference = null;
        $subscriptionRequest->amount = (float) ($pricing[$plan] ?? 0);
        $subscriptionRequest->status = 'pending';
        $subscriptionRequest->metadata = json_encode(['source' => 'get_endpoint']);
        $subscriptionRequest->save();
        
        return "Created subscription request ID: " . $subscriptionRequest->id . " for plan: " . ucfirst($plan) . " - Amount: $" . number_format($subscriptionRequest->amount, 2);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Test endpoint for subscription requests
Route::get('/test-subscription-request', function () {
    try {
        $tenant = \App\Models\Tenant::first();
        if (!$tenant) {
            return 'No tenant found';
        }
        
        // Use exact same pricing as pricing page
        $pesoRate = env('PESO_RATE', 55);
        $pricing = [
            'basic' => 29 * $pesoRate,     // ₱1,595
            'standard' => 79 * $pesoRate,  // ₱4,345  
            'premium' => 149 * $pesoRate,  // ₱8,195
        ];
        $plans = ['basic', 'standard', 'premium'];
        $plan = $plans[array_rand($plans)]; // Random plan
        $amount = (float) $pricing[$plan];
        
        $request = new \App\Models\SubscriptionRequest();
        $request->tenant_id = $tenant->tenant_id;
        $request->requested_plan = $plan;
        $request->payment_method = null;
        $request->payment_reference = null;
        $request->amount = $amount;
        $request->status = 'pending';
        $request->metadata = json_encode(['test' => true]);
        $request->save();
        
        return "Created test subscription request ID: " . $request->id . " for plan: " . ucfirst($plan) . " - Amount: $" . number_format($amount, 2);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

