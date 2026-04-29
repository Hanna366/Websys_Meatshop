<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantPageController;
use App\Http\Controllers\TenantUserController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// Public report endpoint - no middleware at all (not even auth)
Route::post('/dashboard/updates/report', [App\Http\Controllers\TenantUpdateController::class, 'report'])
    ->name('tenant.updates.report.public');

// Test public route
Route::get('/test-public', function() {
    return 'Public route works! No auth required.';
});

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.active',
])->group(function () {
    Route::get('/login', [SimpleAuthController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [SimpleAuthController::class, 'login'])->name('tenant.login.post');
    Route::post('/logout', [SimpleAuthController::class, 'logout'])->name('tenant.logout');
    Route::get('/forgot-password', [SimpleAuthController::class, 'showForgotPasswordForm'])->name('tenant.password.request');
    Route::post('/forgot-password', [SimpleAuthController::class, 'sendResetLink'])->name('tenant.password.email');
    Route::get('/reset-password/{token}', [SimpleAuthController::class, 'showResetPasswordForm'])->name('tenant.password.reset');
    Route::post('/reset-password', [SimpleAuthController::class, 'resetPassword'])->name('tenant.password.update');

    Route::get('/', function () {
        return redirect('/login');
    })->name('tenant.home');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [TenantPageController::class, 'dashboard'])->name('tenant.dashboard');
        Route::get('/pricing', [SubscriptionController::class, 'index'])
            ->middleware('tenant.owner')
            ->name('tenant.pricing');

        Route::post('/subscription/process', [SubscriptionController::class, 'processSubscription'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.process');
        Route::post('/subscription/request', [SubscriptionController::class, 'requestSubscription'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.request');
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.cancel');
        Route::post('/subscription/renew', [SubscriptionController::class, 'renew'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.renew');
        Route::get('/subscription/status', [SubscriptionController::class, 'status'])
            ->name('tenant.subscription.status');
        Route::get('/create-subscription/{plan}', function ($plan) {
            // Use the actual tenant from the current domain
            $tenant = tenant();
            if (!$tenant) {
                return 'No tenant found for this domain';
            }
            $pesoRate = env('PESO_RATE', 55);
            $pricing = [
                'basic' => 29 * $pesoRate,
                'standard' => 79 * $pesoRate,
                'premium' => 149 * $pesoRate,
            ];
            $request = new \App\Models\SubscriptionRequest();
            $request->tenant_id = $tenant->tenant_id;
            $request->requested_plan = $plan;
            $request->amount = (float) ($pricing[$plan] ?? 0);
            $request->status = 'pending';
            $request->metadata = json_encode(['source' => 'tenant_page', 'domain' => request()->getHost()]);
            $request->save();
            return "Created subscription request ID: " . $request->id . " for tenant: " . $tenant->tenant_id . " plan: " . ucfirst($plan);
        });
        Route::get('/subscription/billing', [SubscriptionController::class, 'billingPage'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.billing');
        Route::get('/subscription/billing/data', [SubscriptionController::class, 'billingData'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.billing.data');
        Route::put('/subscription/settings', [SubscriptionController::class, 'updateSettings'])
            ->middleware('tenant.owner')
            ->name('tenant.subscription.settings');

        Route::get('/products', [TenantPageController::class, 'products'])
            ->middleware(['subscription', 'tenant.owner'])
            ->name('tenant.products');

        // Allow users with the modular `inventory.view` permission (admins also have all permissions)
        // Use RBAC fallback middleware so tenants that haven't been seeded with
        // Spatie permissions yet still work via the legacy role-based checks.
        Route::get('/inventory', [TenantPageController::class, 'inventory'])
            ->middleware(['subscription', 'rbac.permission:inventory.view'])
            ->name('tenant.inventory');

        Route::get('/sales', [TenantPageController::class, 'sales'])
            ->middleware(['subscription:pos_access', 'tenant.pos'])
            ->name('tenant.sales');

        Route::get('/customers', [TenantPageController::class, 'customers'])
            ->middleware(['subscription:customer_management', 'tenant.owner'])
            ->name('tenant.customers');

        Route::get('/suppliers', [TenantPageController::class, 'suppliers'])
            ->middleware(['subscription:supplier_management', 'tenant.owner'])
            ->name('tenant.suppliers');

        Route::get('/reports', [TenantPageController::class, 'reports'])
            ->middleware(['subscription:advanced_analytics', 'tenant.owner'])
            ->name('tenant.reports');

        Route::get('/settings', [TenantPageController::class, 'settings'])
            ->middleware(['subscription:custom_branding', 'tenant.owner'])
            ->name('tenant.settings');

        Route::post('/settings/users', [TenantPageController::class, 'storeUser'])
            ->middleware(['subscription:custom_branding', 'tenant.owner'])
            ->name('tenant.users.store');

        Route::put('/settings/users/{userId}', [TenantPageController::class, 'updateUser'])
            ->middleware(['subscription:custom_branding', 'tenant.owner'])
            ->name('tenant.settings.users.update');

        Route::patch('/settings/users/{userId}/status', [TenantPageController::class, 'toggleUserStatus'])
            ->middleware(['subscription:custom_branding', 'tenant.owner'])
            ->name('tenant.settings.users.status');

        // User Management (owner and manager only)
        Route::get('/users', [TenantUserController::class, 'index'])
            ->name('tenant.users.index');
        Route::get('/users/create', [TenantUserController::class, 'create'])
            ->name('tenant.users.create');
        Route::post('/users', [TenantUserController::class, 'store'])
            ->name('tenant.users.store');
        Route::get('/users/{id}', [TenantUserController::class, 'show'])
            ->name('tenant.users.show');
        Route::get('/users/{id}/edit', [TenantUserController::class, 'edit'])
            ->name('tenant.users.edit');
        Route::put('/users/{id}', [TenantUserController::class, 'update'])
            ->name('tenant.users.update');
        Route::post('/users/{id}/reset-password', [TenantUserController::class, 'resetPassword'])
            ->name('tenant.users.reset-password');
        Route::delete('/users/{id}', [TenantUserController::class, 'destroy'])
            ->name('tenant.users.destroy');

        Route::get('/profile', function () {
            return view('profile');
        })->name('tenant.profile');

        // Tenant System Updates (in-tenant routes) — ensure these are available on tenant domains
        Route::get('/dashboard/updates', [App\Http\Controllers\TenantUpdateController::class, 'index'])->name('tenant.updates.index');
        Route::match(['get', 'post'], '/dashboard/updates/request', [App\Http\Controllers\TenantUpdateController::class, 'requestUpdate'])
            ->name('tenant.updates.request');
        // Note: POST /dashboard/updates/report is public route above (no auth required)
        Route::get('/dashboard/updates/history', [App\Http\Controllers\TenantUpdateController::class, 'history'])->name('tenant.updates.history');
    });
});
