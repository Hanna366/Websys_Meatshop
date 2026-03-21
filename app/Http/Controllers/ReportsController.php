<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->reports->dashboard((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['dashboard' => $data]]);
    }

    public function sales(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->reports->sales((string) $user?->tenant_id, $request->only(['days']));

        return response()->json(['success' => true, 'data' => ['sales' => $data]]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->reports->inventory((string) $user?->tenant_id);

        return response()->json(['success' => true, 'data' => ['inventory' => $data]]);
    }

    public function customers(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->reports->customers((string) $user?->tenant_id, (int) $request->query('limit', 50));

        return response()->json(['success' => true, 'data' => ['customers' => $data]]);
    }

    public function suppliers(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->reports->suppliers((string) $user?->tenant_id, (int) $request->query('limit', 50));

        return response()->json(['success' => true, 'data' => ['suppliers' => $data]]);
    }

    public function export(Request $request): JsonResponse
    {
        $user = $request->user();
        $meta = $this->reports->exportMeta((string) $user?->tenant_id, $request->query());

        return response()->json([
            'success' => true,
            'message' => 'Export prepared.',
            'data' => ['export' => $meta],
        ]);
    }
}
