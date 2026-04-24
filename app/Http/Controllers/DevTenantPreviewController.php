<?php

namespace App\Http\Controllers;

use App\Models\UpdateLog;
use App\Models\Version;
use App\Models\UpdateRequest;
use Stancl\Tenancy\Database\Models\Tenant as TenancyTenant;

class DevTenantPreviewController extends Controller
{
    /**
     * Render the tenant updates page without requiring tenancy/auth — dev-only
     */
    public function preview($id)
    {
        $tenant = TenancyTenant::where('tenant_id', $id)->orWhere('id', $id)->first();
        if (! $tenant) {
            return response('Tenant not found', 404);
        }

        $tenantId = $tenant->tenant_id ?? $tenant->id;

        $lastLog = UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first();
        $installedVersion = $lastLog->to_version ?? Version::getCurrentVersion();

        $latest = Version::where('status', 'stable')->orderBy('release_date', 'desc')->first();
        $latestVersion = $latest->version ?? Version::getCurrentVersion();

        $updateAvailable = version_compare($latestVersion, $installedVersion, '>');

        $myRequests = UpdateRequest::where('tenant_id', $tenantId)->orderBy('requested_at', 'desc')->get();

        return view('tenant.updates', [
            'installedVersion' => $installedVersion,
            'latestVersion' => $latestVersion,
            'updateAvailable' => $updateAvailable,
            'latestRelease' => $latest,
            'lastLog' => $lastLog,
            'myRequests' => $myRequests,
            'dev_preview_tenant' => $tenant,
        ]);
    }
}
