<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $user = session('user');

        // If current user is admin, show all tenants.
        // Otherwise, show only the tenant they belong to.
        if ($user && ($user['role'] ?? '') === 'admin') {
            $tenants = Tenant::orderBy('created_at', 'desc')->get();
        } else {
            $tenantId = $user['tenant_id'] ?? null;
            $tenants = $tenantId ? Tenant::where('tenant_id', $tenantId)->get() : collect();
        }

        return view('tenants.index', [
            'tenants' => $tenants,
        ]);
    }

    public function show(string $tenantId)
    {
        $tenant = Tenant::where('tenant_id', $tenantId)->firstOrFail();

        return view('tenants.show', [
            'tenant' => $tenant,
        ]);
    }
}

