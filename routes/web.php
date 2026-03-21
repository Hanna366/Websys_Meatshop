<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Models\Tenant;

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

Route::get('/', function () {
    return view('central.home', [
        'tenants' => Tenant::orderBy('created_at', 'desc')->limit(15)->get(),
    ]);
})->name('central.home');

Route::get('/central', function () {
    return view('central.home', [
        'tenants' => Tenant::orderBy('created_at', 'desc')->limit(15)->get(),
    ]);
})->name('central.framework');

Route::get('/login', [SimpleAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SimpleAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [SimpleAuthController::class, 'logout'])->name('logout');

// Public signup to create tenant from selected plan.
Route::get('/account/create', [TenantController::class, 'create'])->name('tenants.create');
Route::post('/account/create', [TenantController::class, 'store'])->name('tenants.store');

// Central tenant management menu entries.
Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
Route::get('/tenant/{tenantId}', [TenantController::class, 'show'])->name('tenants.show');
Route::post('/tenant/{tenantId}/status', [TenantController::class, 'updateStatus'])->name('tenants.updateStatus');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('central.home', [
            'tenants' => Tenant::orderBy('created_at', 'desc')->limit(15)->get(),
        ]);
    })->name('dashboard');
});

// Pricing page - accessible without authentication
Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');

// Subscription routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::post('/subscription/process', [SubscriptionController::class, 'processSubscription'])->name('subscription.process');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/renew', [SubscriptionController::class, 'renew'])->name('subscription.renew');
    Route::get('/subscription/status', [SubscriptionController::class, 'status'])->name('subscription.status');
    Route::get('/subscription/billing', [SubscriptionController::class, 'billingHistory'])->name('subscription.billing');
    Route::put('/subscription/settings', [SubscriptionController::class, 'updateSettings'])->name('subscription.settings');
});

Route::get('/test', function () {
    return 'Laravel Meat Shop POS is working!';
});

