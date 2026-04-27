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
    Route::get('/subscription-requests', [CentralSubscriptionController::class, 'index'])->name('admin.subscription_requests.index');
    Route::post('/subscription-requests/{id}/approve', [CentralSubscriptionController::class, 'approve'])->name('admin.subscription_requests.approve');
    Route::post('/subscription-requests/{id}/reject', [CentralSubscriptionController::class, 'reject'])->name('admin.subscription_requests.reject');
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

// Tenant-facing System Updates UI (read-only + reporting)
$tenantMiddleware = ['auth', InitializeTenancyByQuery::class, 'tenant.active'];
// In non-debug (production-like) environments, initialize tenancy by domain and prevent central-domain access
if (! config('app.debug')) {
    // Wrap domain initialization to catch tenant-not-found gracefully
    $tenantMiddleware[] = SuppressTenantNotFoundInDebug::class;
    $tenantMiddleware[] = InitializeTenancyByDomain::class;
    $tenantMiddleware[] = PreventAccessFromCentralDomains::class;
}

Route::middleware($tenantMiddleware)->group(function () {
    Route::get('/dashboard/updates', [App\Http\Controllers\TenantUpdateController::class, 'index'])->name('tenant.updates.index');
    Route::post('/dashboard/updates/request', [App\Http\Controllers\TenantUpdateController::class, 'requestUpdate'])->name('tenant.updates.request');
    Route::post('/dashboard/updates/report', [App\Http\Controllers\TenantUpdateController::class, 'report'])->name('tenant.updates.report');
    Route::get('/dashboard/updates/history', [App\Http\Controllers\TenantUpdateController::class, 'history'])->name('tenant.updates.history');

    // Tenant support routes
    Route::get('/dashboard/support', [App\Http\Controllers\SupportTicketController::class, 'index'])->name('tenant.support.index');
    Route::post('/dashboard/support', [App\Http\Controllers\SupportTicketController::class, 'store'])->name('tenant.support.store');
});

