<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $user = session('user');

        // Central app should always be able to list tenants for management.
        if (!$user || ($user['role'] ?? '') === 'admin') {
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

    public function create()
    {
        return view('account.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'business_phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:1000',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'plan' => 'required|in:basic,standard,premium,enterprise',
            'domain' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        $tenant = TenantService::createTenant([
            'business_name' => $validated['business_name'],
            'business_email' => $validated['business_email'],
            'business_phone' => $validated['business_phone'] ?? null,
            'business_address' => $validated['business_address'] ?? null,
            'admin_name' => $validated['admin_name'],
            'admin_email' => $validated['admin_email'],
            'plan' => $validated['plan'],
            'domain' => $validated['domain'] ?? null,
            'password' => $validated['password'] ?? null,
            'subscription' => [
                'plan' => $validated['plan'],
                'status' => 'active',
            ],
        ]);

        return redirect()->route('tenants.show', $tenant->tenant_id)
            ->with('success', 'Tenant created successfully.');
    }

    public function updateStatus(Request $request, string $tenantId)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended,unpaid',
            'payment_status' => 'nullable|in:paid,unpaid,overdue',
            'suspended_message' => 'nullable|string|max:500',
            'domain' => 'nullable|string|max:255',
        ]);

        TenantService::updateTenantLifecycle($tenantId, [
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'] ?? ($validated['status'] === 'unpaid' ? 'unpaid' : 'paid'),
            'suspended_message' => $validated['suspended_message'] ?? 'Please contact your administrator.',
            'domain' => $validated['domain'] ?? null,
        ]);

        return redirect()->route('tenants.show', $tenantId)
            ->with('success', 'Tenant status updated.');
    }
}

