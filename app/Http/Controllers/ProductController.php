<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PricingService;
use App\Services\ProductService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly PricingService $pricing
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $products = $this->products->listForTenant($user->tenant_id, $request->only(['category', 'status', 'search', 'limit']));

        return response()->json(['success' => true, 'data' => ['products' => $products]]);
    }

    public function show(Request $request, string $product): JsonResponse
    {
        $user = $request->user();
        $productModel = $this->products->findForTenant((string) $user?->tenant_id, $product);

        if (!$productModel) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['product' => $productModel]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:beef,pork,chicken,lamb,seafood,processed,other',
            'subcategory' => 'nullable|string|max:255',
            'pricing' => 'required|array',
            'inventory' => 'required|array',
            'batch_tracking' => 'nullable|array',
            'physical_attributes' => 'nullable|array',
            'supplier_info' => 'nullable|array',
            'images' => 'nullable|array',
            'tags' => 'nullable|array',
            'barcode' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,discontinued',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $currentProductCount = Schema::hasColumn('products', 'tenant_id')
            ? Product::where('tenant_id', (string) $user->tenant_id)->count()
            : Product::count();

        if (!SubscriptionService::isWithinLimit('max_products', $currentProductCount + 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Product limit reached for your current plan. Upgrade to add more products.',
                'data' => [
                    'limit_key' => 'max_products',
                    'current_count' => $currentProductCount,
                    'plan' => SubscriptionService::resolveCurrentPlan(),
                ],
            ], 403);
        }

        $product = $this->products->createForTenant((string) $user->tenant_id, (int) $user->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => ['product' => $product],
        ], 201);
    }

    public function update(Request $request, string $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|in:beef,pork,chicken,lamb,seafood,processed,other',
            'subcategory' => 'nullable|string|max:255',
            'pricing' => 'sometimes|array',
            'inventory' => 'sometimes|array',
            'batch_tracking' => 'sometimes|array',
            'physical_attributes' => 'nullable|array',
            'supplier_info' => 'nullable|array',
            'images' => 'nullable|array',
            'tags' => 'nullable|array',
            'barcode' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive,discontinued',
        ]);

        $user = $request->user();
        $productModel = $this->products->findForTenant((string) $user?->tenant_id, $product);

        if (!$productModel) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $updated = $this->products->update($productModel, (int) $user->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => ['product' => $updated],
        ]);
    }

    public function destroy(Request $request, string $product): JsonResponse
    {
        $user = $request->user();
        $productModel = $this->products->findForTenant((string) $user?->tenant_id, $product);

        if (!$productModel) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $this->products->delete($productModel);

        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }

    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();
        $categories = $this->products->categories((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['categories' => $categories]]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
            'category' => 'nullable|in:beef,pork,chicken,lamb,seafood,processed,other',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $results = $this->products->search(
            (string) $user?->tenant_id,
            (string) $validated['q'],
            $validated['category'] ?? null,
            (int) ($validated['limit'] ?? 25)
        );

        return response()->json([
            'success' => true,
            'data' => ['query' => $validated['q'], 'results' => $results],
        ]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $user = $request->user();
        $products = $this->products->lowStock((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['products' => $products]]);
    }

    public function currentPrice(Request $request, string $product): JsonResponse
    {
        $validated = $request->validate([
            'channel' => 'nullable|string|max:50',
            'quantity' => 'nullable|numeric|min:0.001',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $productModel = $this->products->findForTenant((string) $user->tenant_id, $product);
        if (!$productModel) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $resolved = $this->pricing->resolveCurrentPrice(
            (string) $user->tenant_id,
            (int) $productModel->id,
            (string) ($validated['channel'] ?? 'retail'),
            isset($validated['quantity']) ? (float) $validated['quantity'] : null,
        );

        if (!$resolved) {
            $fallback = (float) ($productModel->pricing['price_per_unit'] ?? 0);
            if ($fallback <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active published price found for this product.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $productModel->id,
                    'product_code' => $productModel->product_code,
                    'price' => $fallback,
                    'currency' => 'PHP',
                    'source' => 'product_pricing_fallback',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => $productModel->id,
                'product_code' => $productModel->product_code,
                'price' => $resolved['price'],
                'currency' => $resolved['currency'],
                'price_list_code' => $resolved['price_list_code'],
                'price_list_name' => $resolved['price_list_name'],
                'effective_from' => $resolved['effective_from'],
                'effective_to' => $resolved['effective_to'],
                'source' => 'price_list',
            ],
        ]);
    }
}
