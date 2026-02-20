<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SimpleAuthController;

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
    return redirect('/login');
});

Route::get('/login', [SimpleAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SimpleAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [SimpleAuthController::class, 'logout'])->name('logout');

Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/products', function () {
        return '<h1>Products</h1><p>Manage your products here</p><a href="/dashboard">Back to Dashboard</a>';
    });
    
    Route::get('/inventory', function () {
        return '<h1>Inventory</h1><p>Manage your inventory here</p><a href="/dashboard">Back to Dashboard</a>';
    });
    
    Route::get('/sales', function () {
        return '<h1>Sales</h1><p>Manage your sales here</p><a href="/dashboard">Back to Dashboard</a>';
    });
    
    Route::get('/customers', function () {
        return '<h1>Customers</h1><p>Manage your customers here</p><a href="/dashboard">Back to Dashboard</a>';
    });
    
    Route::get('/suppliers', function () {
        return '<h1>Suppliers</h1><p>Manage your suppliers here</p><a href="/dashboard">Back to Dashboard</a>';
    });
});

Route::get('/test', function () {
    return 'Laravel Meat Shop POS is working!';
});
