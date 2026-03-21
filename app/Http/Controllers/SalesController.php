<?php

namespace App\Http\Controllers;

use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function __construct(private readonly SalesService $sales)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $sales = $this->sales->listForTenant((string) $user->tenant_id, (int) $request->query('limit', 50));

        return response()->json(['success' => true, 'data' => ['sales' => $sales]]);
    }

    public function show(Request $request, string $sale): JsonResponse
    {
        $user = $request->user();
        $saleModel = $this->sales->findForTenant((string) $user?->tenant_id, $sale);

        if (!$saleModel) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['sale' => $saleModel]]);
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_code' => 'nullable|string|max:255',
            'customer_id' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        try {
            $sale = $this->sales->process((string) $user->tenant_id, (int) $user->id, $validated);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale processed successfully.',
            'data' => ['sale' => $sale],
        ], 201);
    }

    public function void(Request $request, string $sale): JsonResponse
    {
        $user = $request->user();
        $saleModel = $this->sales->findForTenant((string) $user?->tenant_id, $sale);

        if (!$saleModel) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $updated = $this->sales->void($saleModel);

        return response()->json(['success' => true, 'message' => 'Sale voided.', 'data' => ['sale' => $updated]]);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->sales->summary((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['summary' => $summary]]);
    }

    public function dailyReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'nullable|integer|min:1|max:90',
        ]);

        $user = $request->user();
        $report = $this->sales->dailyReport((string) $user?->tenant_id, (int) ($validated['days'] ?? 7));

        return response()->json(['success' => true, 'data' => ['report' => $report]]);
    }
}
