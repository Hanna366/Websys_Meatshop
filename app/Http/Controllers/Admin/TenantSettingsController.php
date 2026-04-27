<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantSettingsController extends Controller
{
    public function edit($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $setting = TenantSetting::firstOrNew(['tenant_id' => $tenant->id]);

        return view('admin.tenants.settings', [
            'tenant' => $tenant,
            'setting' => $setting,
        ]);
    }

    public function update(Request $request, $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        $data = $request->validate([
            'theme' => 'nullable|string|max:100',
            'primary_color' => 'nullable|string|max:20',
            'meta' => 'nullable|array',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("tenants/{$tenant->id}", 'public');
            $data['logo_path'] = $path;
        }

        $attrs = [
            'theme' => $data['theme'] ?? null,
            'primary_color' => $data['primary_color'] ?? null,
            'meta' => $data['meta'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
        ];

        TenantSetting::updateOrCreate(['tenant_id' => $tenant->id], $attrs);

        return redirect()->back()->with('status', 'Tenant settings updated.');
    }
}
