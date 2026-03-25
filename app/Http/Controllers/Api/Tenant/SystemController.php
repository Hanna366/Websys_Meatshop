<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function ping()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'service' => 'tenant-api',
                'status' => 'ok',
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}
