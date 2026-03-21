<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.active',
])->group(function () {
    Route::get('/', function () {
        return view('tenant.home', ['tenant' => tenant()]);
    })->name('tenant.home');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

        Route::get('/products', function () {
            return view('products');
        })->middleware('subscription')->name('tenant.products');

        Route::get('/inventory', function () {
            return view('inventory');
        })->middleware('subscription')->name('tenant.inventory');

        Route::get('/sales', function () {
            return view('sales');
        })->middleware('subscription:pos_access')->name('tenant.sales');

        Route::get('/customers', function () {
            return view('customers');
        })->middleware('subscription:customer_management')->name('tenant.customers');

        Route::get('/suppliers', function () {
            return view('suppliers');
        })->middleware('subscription:supplier_management')->name('tenant.suppliers');

        Route::get('/reports', function () {
            return view('reports');
        })->middleware('subscription:advanced_analytics')->name('tenant.reports');

        Route::get('/settings', function () {
            return view('settings');
        })->middleware('subscription:custom_branding')->name('tenant.settings');

        Route::get('/profile', function () {
            return view('profile');
        })->name('tenant.profile');
    });
});
