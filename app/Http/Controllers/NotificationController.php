<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $this->notifications->settings((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['settings' => $settings, 'notifications' => []]]);
    }

    public function settings(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $this->notifications->settings((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['settings' => $settings]]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_enabled' => 'sometimes|boolean',
            'sms_enabled' => 'sometimes|boolean',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'expiry_warning_days' => 'sometimes|integer|min:0',
        ]);

        $user = $request->user();
        $settings = $this->notifications->updateSettings((string) $user?->tenant_id, $validated);

        return response()->json(['success' => true, 'message' => 'Notification settings updated', 'data' => ['settings' => $settings]]);
    }

    public function sendLowStock(Request $request): JsonResponse
    {
        $message = $this->notifications->lowStockMessage($request->all());

        return response()->json(['success' => true, 'message' => 'Low-stock notification queued', 'data' => $message]);
    }

    public function sendExpiry(Request $request): JsonResponse
    {
        $message = $this->notifications->expiryMessage($request->all());

        return response()->json(['success' => true, 'message' => 'Expiry notification queued', 'data' => $message]);
    }

    public function sendCustomer(Request $request): JsonResponse
    {
        $message = $this->notifications->customerMessage($request->all());

        return response()->json(['success' => true, 'message' => 'Customer notification queued', 'data' => $message]);
    }
}
