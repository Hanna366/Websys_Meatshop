<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenantStatsController extends Controller
{
    public function show(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant context not found for authenticated user.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['stats' => $tenant->getStats()],
        ]);
    }
}
