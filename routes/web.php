<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\SubscriptionController;

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
    // Otherwise redirect to login
    return redirect('/login');
});

Route::get('/login', [SimpleAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SimpleAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [SimpleAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::middleware(['subscription:basic'])->group(function () {
        Route::get('/products', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('products');
        });
    });
    
    Route::middleware(['subscription:standard'])->group(function () {
        Route::get('/inventory', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('inventory');
        });
        
        Route::get('/sales', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('sales');
        });
        
        Route::get('/customers', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('customers');
        });
        
        Route::get('/suppliers', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('suppliers');
        });
    });
    
    Route::middleware(['subscription:premium'])->group(function () {
        Route::get('/reports', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('reports');
        });
        
        Route::get('/settings', function () {
            // Check authentication first
            if (!session('authenticated')) {
                return redirect('/login');
            }
            return view('settings');
        });
    });
    
    Route::get('/profile', function () {
        // Check authentication first
        if (!session('authenticated')) {
            return redirect('/login');
        }
        return view('profile');
    });
    
    Route::get('/pricing', function () {
        // Check authentication first
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
