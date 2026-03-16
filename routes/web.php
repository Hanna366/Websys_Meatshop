<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AccountController;

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
    // If already authenticated, redirect to dashboard
    if (session('authenticated')) {
        return redirect('/dashboard');
    }
    // Otherwise show welcome page
    return view('welcome');
});

Route::get('/login', [SimpleAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SimpleAuthController::class, 'login'])->name('login.post');
Route::get('/auth/google', [SimpleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [SimpleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
Route::post('/logout', [SimpleAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Basic Plan Routes (Products available to all authenticated users)
    Route::get('/products', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('products');
    })->middleware('subscription');
    
    // Standard Plan Routes (require standard features)
    Route::get('/inventory', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('inventory');
    })->middleware('subscription');
    
    Route::get('/sales', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('sales');
    })->middleware('subscription:pos_access');
    
    Route::get('/customers', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('customers');
    })->middleware('subscription:customer_management');
    
    Route::get('/suppliers', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('suppliers');
    })->middleware('subscription:supplier_management');
    
    // Premium Plan Routes (require premium features)
    Route::get('/reports', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('reports');
    })->middleware('subscription:advanced_analytics');
    
    Route::get('/settings', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('settings');
    })->middleware('subscription:custom_branding');
    
    // Routes available to all authenticated users
    Route::get('/profile', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('profile');
    });
    
    Route::get('/pricing', function () {
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('pricing');
    });
    
    // Subscription routes
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

Route::get('/account/create', function () {
    return view('account.create');
})->name('account.create');

Route::post('/account/store', [AccountController::class, 'store'])->name('account.store');
