<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        // This is a central management screen, so it should always show all tenants.
        $tenants = Tenant::orderBy('created_at', 'desc')->get();

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
        $request->merge([
            'domain' => $this->normalizeDomain($request->input('domain')),
        ]);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'business_phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:1000',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'plan' => 'required|in:basic,standard,premium,enterprise',
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->whereNull('deleted_at')],
            'password' => 'nullable|string|min:8',
        ]);

        $domain = $validated['domain'] ?? null;

        $tenant = TenantService::createTenant([
            'business_name' => $validated['business_name'],
            'business_email' => $validated['business_email'],
            'business_phone' => $validated['business_phone'] ?? null,
            'business_address' => $validated['business_address'] ?? null,
            'admin_name' => $validated['admin_name'],
            'admin_email' => $validated['admin_email'],
            'plan' => $validated['plan'],
            'domain' => $domain,
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
        $tenant = Tenant::where('tenant_id', $tenantId)->firstOrFail();

        $request->merge([
            'domain' => $this->normalizeDomain($request->input('domain')),
        ]);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended,unpaid',
            'payment_status' => 'nullable|in:paid,unpaid,overdue',
            'suspended_message' => 'nullable|string|max:500',
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($tenant->id)->whereNull('deleted_at'),
            ],
        ]);

        $domain = $validated['domain'] ?? null;

        TenantService::updateTenantLifecycle($tenantId, [
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'] ?? ($validated['status'] === 'unpaid' ? 'unpaid' : 'paid'),
            'suspended_message' => $validated['suspended_message'] ?? 'Please contact your administrator.',
            'domain' => $domain,
        ]);

        return redirect()->route('tenants.show', $tenantId)
            ->with('success', 'Tenant status updated.');
    }

    private function normalizeDomain(?string $domain): ?string
    {
        if ($domain === null) {
            return null;
        }

        $normalized = trim($domain);
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('#^https?://#i', '', $normalized);
        $normalized = rtrim((string) $normalized, '/');

        return str_ireplace('locasthost', 'localhost', $normalized);
    }
}

