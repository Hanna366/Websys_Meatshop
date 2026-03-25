<?php

namespace App\Http\Controllers\Api\Central;

use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    }
}
