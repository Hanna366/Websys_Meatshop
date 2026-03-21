<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
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
}
