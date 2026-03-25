<?php

namespace App\Http\Controllers\Api\Central;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('tenant_id', $request->user()->tenant_id)
            ->select('id', 'email', 'role', 'profile', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => ['users' => $users],
        ]);
    }

    public function show(Request $request, string $user)
    {
        $userData = User::where('tenant_id', $request->user()->tenant_id)
            ->whereKey($user)
            ->select('id', 'email', 'role', 'profile', 'status', 'created_at')
            ->first();

        if (! $userData) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in tenant scope.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['user' => $userData],
        ]);
    }
}
