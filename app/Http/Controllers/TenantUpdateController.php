<?php

namespace App\Http\Controllers;

use App\Models\Version;
use App\Models\UpdateLog;
use App\Models\SupportTicket;
use App\Services\VersionManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantUpdateController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determine tenant id
        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->id ?? $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        // Installed version (based on last update log for this tenant, fallback to app version)
        $lastLog = null;
        $installedVersion = VersionManagementService::getCurrentVersion();

        if ($tenantId) {
            $lastLog = UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first();
            if ($lastLog && $lastLog->to_version) {
                $installedVersion = $lastLog->to_version;
            }
        }

        // Latest available central version
        $latest = Version::where('status', 'stable')->orderBy('release_date', 'desc')->first();
        $latestVersion = $latest->version ?? VersionManagementService::getCurrentVersion();

        $updateAvailable = version_compare($latestVersion, $installedVersion, '>');

        return view('tenant.updates', [
            'installedVersion' => $installedVersion,
            'latestVersion' => $latestVersion,
            'updateAvailable' => $updateAvailable,
            'latestRelease' => $latest,
            'lastLog' => $lastLog,
        ]);
    }

    /**
     * Show tenant update history
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->id ?? $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        $logs = UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->paginate(20);

        return view('tenant.updates_history', [
            'updateLogs' => $logs,
        ]);
    }

    /**
     * Tenant requests an update (sends a central ticket)
     */
    public function requestUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'target_version' => 'nullable|string'
        ]);

        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->id ?? $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        $lastLog = $tenantId ? UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first() : null;

        SupportTicket::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id ?? null,
            'current_version' => $lastLog->to_version ?? VersionManagementService::getCurrentVersion(),
            'last_update_at' => $lastLog->created_at ?? null,
            'message' => 'Tenant requested update to: ' . ($request->input('target_version') ?? 'latest'),
            'status' => 'open',
            'meta' => ['type' => 'update_request']
        ]);

        return redirect()->back()->with('success', 'Update request submitted. Central admin will review.');
    }

    /**
     * Tenant reports an issue
     */
    public function report(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'message' => 'required|string|min:5|max:2000'
        ]);

        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->id ?? $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        $lastLog = $tenantId ? UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first() : null;

        SupportTicket::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id ?? null,
            'current_version' => $lastLog->to_version ?? VersionManagementService::getCurrentVersion(),
            'last_update_at' => $lastLog->created_at ?? null,
            'message' => $request->input('message'),
            'status' => 'open',
            'meta' => ['reported_via' => 'tenant_updates_ui']
        ]);

        return redirect()->back()->with('success', 'Issue reported. Support will follow up.');
    }
}
