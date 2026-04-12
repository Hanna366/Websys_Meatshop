<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CentralDashboardController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\VersionController;

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
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
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
    Route::post('/tenant/{tenantId}', [TenantController::class, 'update'])->name('tenants.update');
    Route::post('/tenant/{tenantId}/status', [TenantController::class, 'updateStatus'])->name('tenants.updateStatus');
    Route::post('/tenant/{tenantId}/subscription', [TenantController::class, 'updateSubscription'])->name('tenants.updateSubscription');
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

// Logo testing routes
Route::get('/logo/test', [App\Http\Controllers\LogoController::class, 'testLogos']);
Route::get('/logo/generate/{tenantId?}', [App\Http\Controllers\LogoController::class, 'generateLogo']);

