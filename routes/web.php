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

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/products', function () {
        return view('products');
    });
    
    Route::get('/inventory', function () {
        return view('inventory');
    });
    
    Route::get('/sales', function () {
        return view('sales');
    });
    
    Route::get('/customers', function () {
        return view('customers');
    });
    
    Route::get('/suppliers', function () {
        return view('suppliers');
    });
    
    Route::get('/reports', function () {
        return view('reports');
    });
    
    Route::get('/settings', function () {
        return view('settings');
    });
    
    Route::get('/pricing', function () {
        return view('pricing');
    });
    
    Route::get('/profile', function () {
        return view('profile');
    });
});

Route::get('/test', function () {
    return 'Laravel Meat Shop POS is working!';
});
