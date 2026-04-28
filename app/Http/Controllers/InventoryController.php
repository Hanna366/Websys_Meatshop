<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly ProductService $products
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $inventory = $this->inventory->listInventory((string) $user->tenant_id);

        return response()->json(['success' => true, 'data' => ['inventory' => $inventory]]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->inventory->stats((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['stats' => $stats]]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $user = $request->user();
        $alerts = $this->inventory->alerts((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['alerts' => $alerts]]);
    }

    public function productBatches(Request $request, string $product): JsonResponse
    {
        $user = $request->user();
        $productModel = $this->products->findForTenant((string) $user?->tenant_id, $product);
        if (!$productModel) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $batches = $this->inventory->productBatches($productModel);

        return response()->json(['success' => true, 'data' => ['product_id' => $productModel->id, 'batches' => $batches]]);
    }

    public function addBatch(Request $request): JsonResponse
    {
        // Cashiers cannot add stock
        if ($request->user()->role === 'cashier') {
            return response()->json(['success' => false, 'message' => 'You do not have permission to add stock.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required',
            'batch_code' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        $user = $request->user();
        $product = $this->products->findForTenant((string) $user?->tenant_id, (string) $validated['product_id']);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $batch = $this->inventory->addBatch($product, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Batch added successfully.',
            'data' => ['batch' => $batch],
        ], 201);
    }

    public function updateBatch(Request $request, string $batch): JsonResponse
    {
        // Cashiers cannot update batches
        if ($request->user()->role === 'cashier') {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update stock.'], 403);
        }

        $validated = $request->validate([
            'batch_code' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        $user = $request->user();
        $batchModel = $this->inventory->findBatchByIdentifier((string) $user?->tenant_id, $batch);
        if (!$batchModel) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        }

        $updated = $this->inventory->updateBatch($batchModel, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Batch updated successfully.',
            'data' => ['batch' => $updated],
        ]);
    }

    public function recordWaste(Request $request, string $batch): JsonResponse
    {
        // Cashiers cannot record waste
        if ($request->user()->role === 'cashier') {
            return response()->json(['success' => false, 'message' => 'You do not have permission to record waste.'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $batchModel = $this->inventory->findBatchByIdentifier((string) $user?->tenant_id, $batch);
        if (!$batchModel) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        }

        $updated = $this->inventory->recordWaste(
            $batchModel,
            (float) $validated['quantity'],
            $validated['reason'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Waste recorded successfully.',
            'data' => ['batch' => $updated],
        ]);
    }
}
