<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantPageController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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
        Route::get('/pricing', [SubscriptionController::class, 'index'])->name('tenant.pricing');

        Route::post('/subscription/process', [SubscriptionController::class, 'processSubscription'])->name('tenant.subscription.process');
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('tenant.subscription.cancel');
        Route::post('/subscription/renew', [SubscriptionController::class, 'renew'])->name('tenant.subscription.renew');
        Route::get('/subscription/status', [SubscriptionController::class, 'status'])->name('tenant.subscription.status');
        Route::get('/subscription/billing', [SubscriptionController::class, 'billingPage'])->name('tenant.subscription.billing');
        Route::get('/subscription/billing/data', [SubscriptionController::class, 'billingData'])->name('tenant.subscription.billing.data');
        Route::put('/subscription/settings', [SubscriptionController::class, 'updateSettings'])->name('tenant.subscription.settings');

        Route::get('/products', [TenantPageController::class, 'products'])->middleware('subscription')->name('tenant.products');

        Route::get('/inventory', [TenantPageController::class, 'inventory'])->middleware('subscription')->name('tenant.inventory');

        Route::get('/sales', [TenantPageController::class, 'sales'])->middleware('subscription:pos_access')->name('tenant.sales');

        Route::get('/customers', [TenantPageController::class, 'customers'])->middleware('subscription:customer_management')->name('tenant.customers');

        Route::get('/suppliers', [TenantPageController::class, 'suppliers'])->middleware('subscription:supplier_management')->name('tenant.suppliers');

        Route::get('/reports', [TenantPageController::class, 'reports'])->middleware('subscription:advanced_analytics')->name('tenant.reports');

        Route::get('/settings', function () {
            return view('settings');
        })->middleware('subscription:custom_branding')->name('tenant.settings');

        Route::get('/profile', function () {
            return view('profile');
        })->name('tenant.profile');
    });
});
