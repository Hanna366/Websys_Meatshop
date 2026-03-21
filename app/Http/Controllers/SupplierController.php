<?php

namespace App\Http\Controllers;

use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $suppliers = $this->suppliers->listForTenant((string) $user->tenant_id);

        return response()->json(['success' => true, 'data' => ['suppliers' => $suppliers]]);
    }

    public function show(Request $request, string $supplier): JsonResponse
    {
        $user = $request->user();
        $supplierModel = $this->suppliers->findForTenant((string) $user?->tenant_id, $supplier);
        if (!$supplierModel) {
            return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['supplier' => $supplierModel]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|array',
            'details' => 'nullable|array',
            'performance_metrics' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $supplier = $this->suppliers->createForTenant((string) $user->tenant_id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully.',
            'data' => ['supplier' => $supplier],
        ], 201);
    }

    public function update(Request $request, string $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|array',
            'details' => 'nullable|array',
            'performance_metrics' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        $user = $request->user();
        $supplierModel = $this->suppliers->findForTenant((string) $user?->tenant_id, $supplier);
        if (!$supplierModel) {
            return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
        }

        $updated = $this->suppliers->update($supplierModel, $validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated.', 'data' => ['supplier' => $updated]]);
    }

    public function destroy(Request $request, string $supplier): JsonResponse
    {
        $user = $request->user();
        $supplierModel = $this->suppliers->findForTenant((string) $user?->tenant_id, $supplier);
        if (!$supplierModel) {
            return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
        }

        $this->suppliers->delete($supplierModel);

        return response()->json(['success' => true, 'message' => 'Supplier deleted.']);
    }

    public function updateQualityScore(Request $request, string $supplier): JsonResponse
    {
        $validated = $request->validate([
            'quality_score' => 'required|integer|min:0|max:100',
        ]);

        $user = $request->user();
        $supplierModel = $this->suppliers->findForTenant((string) $user?->tenant_id, $supplier);
        if (!$supplierModel) {
            return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
        }

        $updated = $this->suppliers->updateQualityScore($supplierModel, (int) $validated['quality_score']);

        return response()->json(['success' => true, 'message' => 'Quality score updated.', 'data' => ['supplier' => $updated]]);
    }

    public function performance(Request $request, string $supplier): JsonResponse
    {
        $user = $request->user();
        $supplierModel = $this->suppliers->findForTenant((string) $user?->tenant_id, $supplier);
        if (!$supplierModel) {
            return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['performance' => $this->suppliers->performance($supplierModel)]]);
    }

    public function rankings(Request $request): JsonResponse
    {
        $user = $request->user();
        $rankings = $this->suppliers->rankings((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['rankings' => $rankings]]);
    }
}
