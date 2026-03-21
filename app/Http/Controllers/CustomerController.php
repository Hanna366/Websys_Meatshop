<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $customers = $this->customers->listForTenant((string) $user->tenant_id);

        return response()->json(['success' => true, 'data' => ['customers' => $customers]]);
    }

    public function show(Request $request, string $customer): JsonResponse
    {
        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);

        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['customer' => $customerModel]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_code' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|array',
            'preferences' => 'nullable|array',
            'loyalty' => 'nullable|array',
            'purchasing_history' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant_id) {
            return response()->json(['success' => false, 'message' => 'Tenant context is required.'], 401);
        }

        $customer = $this->customers->createForTenant((string) $user->tenant_id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'data' => ['customer' => $customer],
        ], 201);
    }

    public function update(Request $request, string $customer): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|array',
            'preferences' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);
        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $updated = $this->customers->update($customerModel, $validated);

        return response()->json(['success' => true, 'message' => 'Customer updated.', 'data' => ['customer' => $updated]]);
    }

    public function destroy(Request $request, string $customer): JsonResponse
    {
        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);
        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $this->customers->delete($customerModel);

        return response()->json(['success' => true, 'message' => 'Customer deleted.']);
    }

    public function purchaseHistory(Request $request, string $customer): JsonResponse
    {
        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);
        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['history' => $this->customers->purchaseHistory($customerModel)]]);
    }

    public function analytics(Request $request, string $customer): JsonResponse
    {
        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);
        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['analytics' => $this->customers->analytics($customerModel)]]);
    }

    public function addLoyaltyPoints(Request $request, string $customer): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);
        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $updated = $this->customers->addLoyaltyPoints($customerModel, (int) $validated['points']);

        return response()->json(['success' => true, 'message' => 'Loyalty points added.', 'data' => ['customer' => $updated]]);
    }

    public function redeemLoyaltyPoints(Request $request, string $customer): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $customerModel = $this->customers->findForTenant((string) $user?->tenant_id, $customer);
        if (!$customerModel) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $updated = $this->customers->redeemLoyaltyPoints($customerModel, (int) $validated['points']);

        return response()->json(['success' => true, 'message' => 'Loyalty points redeemed.', 'data' => ['customer' => $updated]]);
    }
}
