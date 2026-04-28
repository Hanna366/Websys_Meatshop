<?php

namespace App\Http\Controllers;

use App\Models\Version;
use App\Models\UpdateLog;
use App\Models\UpdateRequest;
use App\Models\SupportTicket;
use App\Models\TenantUpdate;
use App\Models\TenantUpdateRequest;
use App\Models\TenantSupportTicket;
use App\Services\VersionManagementService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TenantUpdateController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determine tenant id
        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->tenant_id ?? $tenant->id ?? null; // prefer UUID (`tenant_id`) for central consistency
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        // Get last log for display and current version
        $lastLog = null;
        $installedVersion = VersionManagementService::getCurrentVersion(); // fallback
        
        if ($tenantId) {
            $tenant = \App\Models\Tenant::where('tenant_id', $tenantId)->first();
            if ($tenant) {
                $lastLog = UpdateLog::where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->orderBy('completed_at', 'desc')
                    ->first();
                if ($lastLog && $lastLog->to_version) {
                    $installedVersion = $lastLog->to_version;
                }
            }
        }

        // Prefer tenant-scoped update record for 'available' data; fallback to central release info
        $tenantUpdate = null;
        if (function_exists('tenant') && tenant()) {
            try {
                if (Schema::hasTable('tenant_updates')) {
                    $tenantUpdate = TenantUpdate::first();
                }
            } catch (\Exception $e) {
                // If the tenant database isn't migrated yet, ignore and fallback to central
                $tenantUpdate = null;
            }
        }

        if ($tenantUpdate && $tenantUpdate->available_version) {
            $latestVersion = $tenantUpdate->available_version;
            $latestRelease = (object) ['version' => $tenantUpdate->available_version, 'release_notes' => $tenantUpdate->release_notes];
        } else {
            $latest = Version::where('is_stable', true)
                ->where('is_available_to_tenants', true)
                ->orderBy('version', 'desc')
                ->first();
            $latestVersion = $latest->version ?? VersionManagementService::getCurrentVersion();
            $latestRelease = $latest;
        }

        $updateAvailable = version_compare($latestVersion, $installedVersion, '>');

        // Tenant's own update requests (tenant-local entries) - always get fresh data
        try {
            $myRequests = Schema::hasTable('tenant_update_requests') ? TenantUpdateRequest::orderBy('requested_at', 'desc')->get() : collect();
            
            // Filter to show only this tenant's requests
            if ($tenantId) {
                $myRequests = $myRequests->where('tenant_id', $tenantId);
            }
        } catch (\Exception $e) {
            $myRequests = collect();
        }

        // All central versions (so tenants can choose which release to request)
        $versions = Version::orderBy('release_date', 'desc')->get();

        return view('tenant.updates', [
            'installedVersion' => $installedVersion,
            'latestVersion' => $latestVersion,
            'updateAvailable' => $updateAvailable,
            'latestRelease' => $latestRelease,
            'lastLog' => $lastLog,
            'myRequests' => $myRequests,
            'versions' => $versions,
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
            $tenantId = $tenant->tenant_id ?? $tenant->id ?? null;
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
            'target_version' => 'required|string'
        ]);

        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->tenant_id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        // Debug: Make sure we have a tenant ID
        if (!$tenantId) {
            return redirect()->back()->withErrors(['error' => 'Unable to determine tenant context. Please try again.']);
        }

        $lastLog = $tenantId ? UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first() : null;

        $target = $request->input('target_version');

        // Validate requested version exists and meets availability criteria
        $versionQuery = Version::where('version', $target);
        // If DB doesn't have the columns yet, fall back to status-based check
        if (Schema::hasColumn('versions', 'is_stable')) {
            $versionQuery->where('is_stable', true)
                         ->where('is_available_to_tenants', true)
                         ->where('is_deprecated', false);
        } else {
            $versionQuery->where('status', 'stable');
        }

        $versionInfo = $versionQuery->first();

        $currentVersion = $lastLog->to_version ?? VersionManagementService::getCurrentVersion();

        if (! $versionInfo || ! version_compare($versionInfo->version, $currentVersion, '>')) {
            return redirect()->back()->withErrors(['target_version' => 'Requested version is invalid or not available.']);
        }

        // Create tenant-local update request record
        TenantUpdateRequest::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id ?? null,
            'current_version' => $currentVersion,
            'requested_version' => $versionInfo->version,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // Also create central update request record for admin visibility
        UpdateRequest::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id ?? null,
            'current_version' => $currentVersion,
            'requested_version' => $versionInfo->version,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Update request submitted for admin review.');
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
            // Prefer the tenant UUID/identifier used by Central (`tenant_id`) so central
            // records can be joined to the Tenant model. Fall back to numeric id when
            // UUID is not available.
            $tenantId = $tenant->tenant_id ?? $tenant->id ?? null;
        } else {
            $tenantId = $user->tenant_id ?? null;
        }

        $lastLog = $tenantId ? UpdateLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first() : null;

        TenantSupportTicket::create([
            'user_id' => $user->id ?? null,
            'current_version' => $lastLog->to_version ?? VersionManagementService::getCurrentVersion(),
            'message' => $request->input('message'),
            'status' => 'open',
            'meta' => ['reported_via' => 'tenant_updates_ui'],
        ]);

        // Also create a central support ticket so Central Admins can view reported issues
        try {
            SupportTicket::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id ?? null,
                'current_version' => $lastLog->to_version ?? VersionManagementService::getCurrentVersion(),
                'message' => $request->input('message'),
                'status' => 'open',
                'meta' => ['reported_via' => 'tenant_updates_ui'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed creating central SupportTicket: '.$e->getMessage());
        }
        return redirect()->back()->with('success', 'Issue reported locally. Support will follow up.');
    }
}
