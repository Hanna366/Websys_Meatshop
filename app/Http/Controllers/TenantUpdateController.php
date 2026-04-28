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

        // Check GitHub for newer versions that might not be synced yet
        try {
            $check = \App\Services\VersionManagementService::checkForUpdates($tenantId);
            if (is_array($check) && !empty($check['latest_version'])) {
                $ghVersion = ltrim($check['latest_version'], 'v');
                // If GitHub has a newer version, use it as the latest
                if (version_compare($ghVersion, $latestVersion, '>')) {
                    $latestVersion = $ghVersion;
                    $latestRelease = (object) [
                        'version' => $ghVersion,
                        'release_name' => $check['update_info']['release_name'] ?? null,
                        'description' => $check['update_info']['description'] ?? null,
                        'is_stable' => true,
                        'is_available_to_tenants' => true,
                        'is_deprecated' => false,
                        'release_date' => $check['update_info']['published_at'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::debug('GitHub version check for tenant failed: ' . $e->getMessage());
        }

        $updateAvailable = version_compare($latestVersion, $installedVersion, '>');

        // If central has a newer version, ensure tenant-scoped pointer exists so UI shows it
        if (function_exists('tenant') && tenant()) {
            try {
                if (Schema::hasTable('tenant_updates') && version_compare($latestVersion, $installedVersion, '>')) {
                    $notes = null;
                    if (is_object($latestRelease)) {
                        $notes = $latestRelease->description ?? ($latestRelease->release_notes ?? null);
                    } elseif (is_array($latestRelease)) {
                        $notes = $latestRelease['description'] ?? ($latestRelease['release_notes'] ?? null);
                    }

                    $existing = TenantUpdate::first();
                    if ($existing) {
                        $existing->update([
                            'available_version' => $latestVersion,
                            'release_notes' => $notes,
                            'last_checked_at' => now(),
                        ]);
                    } else {
                        TenantUpdate::create([
                            'current_version' => $installedVersion,
                            'available_version' => $latestVersion,
                            'release_notes' => $notes,
                            'last_checked_at' => now(),
                            'force_update' => false,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::debug('Failed to write tenant_updates from TenantUpdateController: ' . $e->getMessage());
            }
        }

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

        // If there's a GitHub-sourced latest release that isn't present in the local
        // `versions` table, surface it to tenants so they can request it.
        try {
            $check = \App\Services\VersionManagementService::checkForUpdates($tenantId);
            if (is_array($check) && isset($check['source']) && $check['source'] === 'github' && !empty($check['update_info'])) {
                $ghVersion = ltrim($check['update_info']['version'] ?? ($check['latest_version'] ?? ''), 'v');

                // Only add if it's newer than installed and not already in the versions collection
                $exists = $versions->firstWhere('version', $ghVersion);
                if (!$exists && version_compare($ghVersion, $installedVersion, '>')) {
                    $gh = new \stdClass();
                    $gh->version = $ghVersion;
                    $gh->release_name = $check['update_info']['release_name'] ?? null;
                    $gh->description = $check['update_info']['description'] ?? null;
                    $gh->is_stable = true;
                    $gh->is_available_to_tenants = true;
                    $gh->is_deprecated = false;
                    $gh->release_date = $check['update_info']['published_at'] ?? null;

                    // Append to the collection so the view's filtering will pick this up
                    $versions->push($gh);
                }
            }
        } catch (\Exception $e) {
            // Don't let GitHub checks break tenant UI; fall back to local versions only
            \Log::debug('Version check failed for tenant updates: ' . $e->getMessage());
        }

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

        // If version not found locally, check GitHub
        if (! $versionInfo) {
            try {
                $ghReleases = \App\Services\VersionManagementService::getGitHubReleases();
                foreach ($ghReleases as $release) {
                    $releaseVersion = ltrim($release['tag_name'] ?? '', 'v');
                    if ($releaseVersion === $target) {
                        // Found on GitHub, create a temporary version object
                        $versionInfo = (object) [
                            'version' => $target,
                            'release_name' => $release['name'] ?? null,
                            'description' => $release['body'] ?? null,
                            'is_stable' => empty($release['is_prerelease']),
                            'is_available_to_tenants' => true,
                            'is_deprecated' => false,
                        ];
                        break;
                    }
                }
            } catch (\Exception $e) {
                \Log::debug('GitHub check for version request failed: ' . $e->getMessage());
            }
        }

        if (! $versionInfo || ! version_compare($target, $currentVersion, '>')) {
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
        $centralRequest = UpdateRequest::create([
            'tenant_id' => $tenantId,
            'user_id' => $user->id ?? null,
            'current_version' => $currentVersion,
            'requested_version' => $versionInfo->version,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Update request submitted for admin review. Request ID: #' . $centralRequest->id);
    }

    /**
     * Tenant reports an issue
     */
    public function report(Request $request)
    {
        \Log::info('REPORT METHOD CALLED', ['url' => $request->url(), 'method' => $request->method(), 'input' => $request->all()]);
        
        $user = Auth::user();
        $centralUser = auth('web')->user(); // Get central user if available

        \Log::info('Validating request', ['user' => $user ? $user->id : null, 'centralUser' => $centralUser ? $centralUser->id : null]);

        $request->validate([
            'message' => 'required|string|min:2|max:2000'
        ]);

        \Log::info('Validation passed');

        // Determine tenant ID - from tenancy context, query param, or user's tenant
        $tenantId = null;
        if (function_exists('tenant') && tenant()) {
            $tenant = tenant();
            $tenantId = $tenant->tenant_id ?? $tenant->id ?? null;
            \Log::info('Tenant ID from tenancy context', ['tenantId' => $tenantId]);
        } elseif ($request->filled('tenant')) {
            $tenantId = $request->input('tenant');
            \Log::info('Tenant ID from request param', ['tenantId' => $tenantId]);
        } elseif ($user && isset($user->tenant_id)) {
            $tenantId = $user->tenant_id;
            \Log::info('Tenant ID from user', ['tenantId' => $tenantId]);
        } elseif ($centralUser && isset($centralUser->tenant_id)) {
            $tenantId = $centralUser->tenant_id;
            \Log::info('Tenant ID from central user', ['tenantId' => $tenantId]);
        }

        // Try to find tenant by ID if not resolved
        if (!$tenantId && $request->filled('tenant_id')) {
            $tenantId = $request->input('tenant_id');
            \Log::info('Tenant ID from tenant_id input', ['tenantId' => $tenantId]);
        }

        \Log::info('Final tenant ID', ['tenantId' => $tenantId]);

        // Get effective user ID (prefer central user if available)
        $effectiveUser = $centralUser ?? $user;
        $userId = $effectiveUser->id ?? null;

        // Get the tenant's current version using the service method
        $currentVersion = $tenantId ? VersionManagementService::getTenantCurrentVersion($tenantId) : VersionManagementService::getCurrentVersion();

        // Create tenant-local ticket if we're in tenant context
        try {
            if (function_exists('tenant') && tenant()) {
                TenantSupportTicket::create([
                    'user_id' => $userId,
                    'current_version' => $currentVersion,
                    'message' => $request->input('message'),
                    'status' => 'open',
                    'meta' => ['reported_via' => 'tenant_updates_ui'],
                ]);
            }
        } catch (\Exception $e) {
            Log::debug('Tenant support ticket creation skipped or failed', ['error' => $e->getMessage()]);
        }

        // Always create a central support ticket so Central Admins can view reported issues
        try {
            Log::info('Creating central support ticket', ['tenant_id' => $tenantId, 'user_id' => $userId, 'current_version' => $currentVersion]);
            $centralTicket = SupportTicket::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'current_version' => $currentVersion,
                'message' => $request->input('message'),
                'status' => 'open',
                'meta' => ['reported_via' => 'tenant_updates_ui'],
            ]);
            Log::info('Central support ticket created', ['ticket_id' => $centralTicket->id]);
        } catch (\Exception $e) {
            Log::error('Failed creating central SupportTicket: '.$e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Failed to create support ticket: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Issue reported successfully. Support will follow up.');
    }
}
