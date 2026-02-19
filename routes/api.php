<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Product Routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
        Route::get('/categories', [ProductController::class, 'categories']);
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/low-stock', [ProductController::class, 'lowStock']);
    });

    // Inventory Routes
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/stats', [InventoryController::class, 'stats']);
        Route::get('/alerts', [InventoryController::class, 'alerts']);
        Route::get('/product/{product}/batches', [InventoryController::class, 'productBatches']);
        Route::post('/batch', [InventoryController::class, 'addBatch']);
        Route::put('/batch/{batch}', [InventoryController::class, 'updateBatch']);
        Route::post('/batch/{batch}/waste', [InventoryController::class, 'recordWaste']);
    });

    // Sales Routes
    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'index']);
        Route::get('/{sale}', [SalesController::class, 'show']);
        Route::post('/', [SalesController::class, 'process']);
        Route::post('/{sale}/void', [SalesController::class, 'void']);
        Route::get('/summary', [SalesController::class, 'summary']);
        Route::get('/daily-report', [SalesController::class, 'dailyReport']);
    });

    // Customer Routes
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/{customer}', [CustomerController::class, 'show']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::put('/{customer}', [CustomerController::class, 'update']);
        Route::delete('/{customer}', [CustomerController::class, 'destroy']);
        Route::get('/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory']);
        Route::get('/{customer}/analytics', [CustomerController::class, 'analytics']);
        Route::post('/{customer}/loyalty/add', [CustomerController::class, 'addLoyaltyPoints']);
        Route::post('/{customer}/loyalty/redeem', [CustomerController::class, 'redeemLoyaltyPoints']);
    });

    // Supplier Routes
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::get('/{supplier}', [SupplierController::class, 'show']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
        Route::put('/{supplier}/quality-score', [SupplierController::class, 'updateQualityScore']);
        Route::get('/{supplier}/performance', [SupplierController::class, 'performance']);
        Route::get('/rankings', [SupplierController::class, 'rankings']);
    });

    // Reports Routes
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [ReportsController::class, 'dashboard']);
        Route::get('/sales', [ReportsController::class, 'sales']);
        Route::get('/inventory', [ReportsController::class, 'inventory']);
        Route::get('/customers', [ReportsController::class, 'customers']);
        Route::get('/suppliers', [ReportsController::class, 'suppliers']);
        Route::get('/export', [ReportsController::class, 'export']);
    });

    // Subscription Routes
    Route::prefix('subscriptions')->group(function () {
        Route::get('/current', [SubscriptionController::class, 'current']);
        Route::get('/plans', [SubscriptionController::class, 'plans']);
        Route::get('/usage', [SubscriptionController::class, 'usage']);
        Route::get('/billing', [SubscriptionController::class, 'billing']);
        Route::post('/', [SubscriptionController::class, 'create']);
        Route::put('/plan', [SubscriptionController::class, 'update']);
        Route::put('/payment-method', [SubscriptionController::class, 'updatePaymentMethod']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/settings', [NotificationController::class, 'settings']);
        Route::put('/settings', [NotificationController::class, 'updateSettings']);
        Route::post('/low-stock', [NotificationController::class, 'sendLowStock']);
        Route::post('/expiry', [NotificationController::class, 'sendExpiry']);
        Route::post('/customer', [NotificationController::class, 'sendCustomer']);
    });

    // Tenant Routes
    Route::prefix('tenants')->group(function () {
        Route::get('/stats', function (Request $request) {
            $tenant = $request->user()->tenant;
            return response()->json([
                'success' => true,
                'data' => ['stats' => $tenant->getStats()]
            ]);
        });
    });

    // User Management Routes
    Route::prefix('users')->group(function () {
        Route::get('/', function (Request $request) {
            $users = \App\Models\User::where('tenant_id', $request->user()->tenant_id)
                ->select('id', 'email', 'role', 'profile', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate(50);
                
            return response()->json([
                'success' => true,
                'data' => ['users' => $users]
            ]);
        });
        Route::get('/{user}', function ($user) {
            $userData = \App\Models\User::find($user);
            return response()->json([
                'success' => true,
                'data' => ['user' => $userData]
            ]);
        });
    });

    // External API Routes (for integrations)
    Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {
        Route::get('/docs', [ApiController::class, 'docs']);
        Route::get('/usage', [ApiController::class, 'usage']);
        Route::get('/products', [ApiController::class, 'products']);
        Route::post('/products', [ApiController::class, 'createProduct']);
        Route::get('/inventory/batches', [ApiController::class, 'inventoryBatches']);
        Route::post('/sales', [ApiController::class, 'createSale']);
        Route::get('/customers', [ApiController::class, 'customers']);
        Route::post('/customers', [ApiController::class, 'createCustomer']);
    });
});

// Public Authentication Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
