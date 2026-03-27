<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\CustomerService;
use App\Services\ProductService;
use App\Services\SalesService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly CustomerService $customers,
        private readonly SalesService $sales
    ) {
    }

    public function docs(): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess(request())) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'API docs endpoint scaffolded',
                'version' => 'v1',
            ],
        ]);
    }

    public function usage(): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess(request())) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'usage' => [
                    'rate_limit' => '60 requests/min',
                    'version' => 'v1',
                    'generated_at' => now()->toIso8601String(),
                ],
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess($request)) {
            return $denied;
        }

        $user = $request->user();
        $products = $this->products->listForTenant((string) $user?->tenant_id, ['limit' => 100]);

        return response()->json(['success' => true, 'data' => ['products' => $products]]);
    }

    public function createProduct(Request $request): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:beef,pork,chicken,lamb,seafood,processed,other',
            'pricing' => 'required|array',
            'inventory' => 'required|array',
            'product_code' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $product = $this->products->createForTenant((string) $user?->tenant_id, (int) $user?->id, $validated);

        return response()->json(['success' => true, 'message' => 'Partner product created', 'data' => ['product' => $product]], 201);
    }

    public function inventoryBatches(Request $request): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess($request)) {
            return $denied;
        }

        $user = $request->user();
        $products = $this->products->listForTenant((string) $user?->tenant_id, ['limit' => 100])->getCollection();
        $batches = $products->flatMap(fn ($product) => $product->batches)->values();

        return response()->json(['success' => true, 'data' => ['batches' => $batches]]);
    }

    public function createSale(Request $request): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        $sale = $this->sales->process((string) $user?->tenant_id, (int) $user?->id, $validated);

        return response()->json(['success' => true, 'message' => 'Partner sale created', 'data' => ['sale' => $sale]], 201);
    }

    public function customers(Request $request): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess($request)) {
            return $denied;
        }

        $user = $request->user();
        $customers = $this->customers->listForTenant((string) $user?->tenant_id, 100);

        return response()->json(['success' => true, 'data' => ['customers' => $customers]]);
    }

    public function createCustomer(Request $request): JsonResponse
    {
        if ($denied = $this->authorizeApiAccess($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|array',
            'preferences' => 'nullable|array',
        ]);

        $user = $request->user();
        $customer = $this->customers->createForTenant((string) $user?->tenant_id, $validated);

        return response()->json(['success' => true, 'message' => 'Partner customer created', 'data' => ['customer' => $customer]], 201);
    }

    private function authorizeApiAccess(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant context is required.',
            ], 401);
        }

        if (!SubscriptionService::hasFeature('api_access')) {
            return response()->json([
                'success' => false,
                'message' => 'API access is not available on your current plan.',
                'data' => ['required_feature' => 'api_access'],
            ], 403);
        }

        $tenant = Tenant::where('tenant_id', (string) $user->tenant_id)->first();
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant record not found.',
            ], 404);
        }

        $usage = is_array($tenant->usage) ? $tenant->usage : [];
        $apiCalls = (int) ($usage['api_calls_this_month'] ?? 0);

        if (!SubscriptionService::isWithinLimit('max_api_calls_per_month', $apiCalls + 1)) {
            return response()->json([
                'success' => false,
                'message' => 'API call limit reached for your current plan.',
                'data' => [
                    'limit_key' => 'max_api_calls_per_month',
                    'current_usage' => $apiCalls,
                    'plan' => SubscriptionService::resolveCurrentPlan(),
                ],
            ], 429);
        }

        $usage['api_calls_this_month'] = $apiCalls + 1;
        $tenant->usage = $usage;
        $tenant->save();

        return null;
    }
}
