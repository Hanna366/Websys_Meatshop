<?php

namespace App\Http\Controllers;

use App\Models\Version;
use App\Models\UpdateLog;
use App\Services\VersionManagementService;
use App\Services\UpdateNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VersionController extends Controller
{
    /**
     * Display version management dashboard
     */
    public function index()
    {
        $currentVersion = VersionManagementService::getCurrentVersion();
        $updateInfo = VersionManagementService::checkForUpdates();
        $versions = Version::orderBy('release_date', 'desc')->get();
        $updateHistory = VersionManagementService::getUpdateHistory();
        $githubReleases = VersionManagementService::getGitHubReleases();

        return view('admin.versions.index', compact(
            'currentVersion',
            'updateInfo',
            'versions',
            'updateHistory',
            'githubReleases'
        ));
    }

    /**
     * Show create version form
     */
    public function create()
    {
        return view('admin.versions.create');
    }

    /**
     * Store new version
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string|max:20|unique:versions,version',
            'release_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:major,minor,patch,hotfix',
            'status' => 'required|in:development,testing,stable,deprecated',
            'release_date' => 'nullable|date',
            'features' => 'nullable|array',
            'fixes' => 'nullable|array',
            'requirements' => 'nullable|array',
            'download_url' => 'nullable|url',
            'checksum' => 'nullable|string|max:32',
            'is_mandatory' => 'boolean',
            'auto_update' => 'boolean',
        ]);

        $version = VersionManagementService::createVersion($validated);

        // If this is a new stable version, notify tenants
        if ($validated['status'] === 'stable') {
            UpdateNotificationService::checkAndNotify();
        }

        return redirect()
            ->route('admin.versions.index')
            ->with('success', "Version {$version->version} created successfully");
    }

    /**
     * Show version details
     */
    public function show(Version $version)
    {
        $updateLogs = UpdateLog::where('to_version', $version->version)
            ->with('tenant')
            ->get();

        return view('admin.versions.show', compact('version', 'updateLogs'));
    }

    /**
     * Show edit version form
     */
    public function edit(Version $version)
    {
        return view('admin.versions.edit', compact('version'));
    }

    /**
     * Update version
     */
    public function update(Request $request, Version $version)
    {
        $validated = $request->validate([
            'release_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:development,testing,stable,deprecated',
            'release_date' => 'nullable|date',
            'features' => 'nullable|array',
            'fixes' => 'nullable|array',
            'requirements' => 'nullable|array',
            'download_url' => 'nullable|url',
            'checksum' => 'nullable|string|max:32',
            'is_mandatory' => 'boolean',
            'auto_update' => 'boolean',
        ]);

        $version->update($validated);

        return redirect()
            ->route('admin.versions.show', $version)
            ->with('success', "Version {$version->version} updated successfully");
    }

    /**
     * Delete version
     */
    public function destroy(Version $version)
    {
        $version->delete();

        return redirect()
            ->route('admin.versions.index')
            ->with('success', "Version {$version->version} deleted successfully");
    }

    /**
     * Check for updates (AJAX endpoint)
     */
    public function checkUpdates()
    {
        $updateInfo = VersionManagementService::checkForUpdates();
        
        return response()->json([
            'success' => true,
            'data' => $updateInfo
        ]);
    }

    /**
     * Download update
     */
    public function downloadUpdate(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string',
            'tenant_id' => 'nullable|integer|exists:tenants,id'
        ]);

        $result = VersionManagementService::downloadUpdate(
            $validated['version'], 
            $validated['tenant_id'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Install update
     */
    public function installUpdate(Request $request)
    {
        $validated = $request->validate([
            'file_path' => 'required|string',
            'version' => 'required|string',
            'tenant_id' => 'nullable|integer|exists:tenants,id'
        ]);

        $result = VersionManagementService::installUpdate(
            $validated['file_path'],
            $validated['version'],
            $validated['tenant_id'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Upload update package
     */
    public function uploadPackage(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string',
            'package' => 'required|file|mimes:zip|max:50000', // 50MB max
            'checksum' => 'nullable|string|max:32'
        ]);

        $file = $request->file('package');
        $fileName = "update-{$validated['version']}.zip";
        $filePath = $file->storeAs('updates', $fileName);

        // Verify checksum if provided
        if ($validated['checksum']) {
            $fileContent = Storage::get($filePath);
            $calculatedChecksum = md5($fileContent);
            
            if ($calculatedChecksum !== $validated['checksum']) {
                Storage::delete($filePath);
                return response()->json([
                    'success' => false,
                    'error' => 'Checksum verification failed'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'file_path' => $filePath,
            'checksum' => $validated['checksum'] ?? md5(Storage::get($filePath))
        ]);
    }

    /**
     * Get update status
     */
    public function getUpdateStatus()
    {
        $pendingUpdates = UpdateLog::where('status', 'pending')
            ->with('tenant')
            ->get();
            
        $activeUpdates = UpdateLog::whereIn('status', ['downloading', 'installing'])
            ->with('tenant')
            ->get();

        return response()->json([
            'pending_updates' => $pendingUpdates,
            'active_updates' => $activeUpdates
        ]);
    }

    /**
     * Sync GitHub releases
     */
    public function syncGitHub()
    {
        $result = VersionManagementService::syncGitHubReleases();
        
        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] 
                ? "Successfully synced {$result['total_releases']} releases. {$result['synced']} new, {$result['updated']} updated."
                : 'Sync failed',
            'data' => $result
        ]);
    }

    /**
     * Clear GitHub cache
     */
    public function clearGitHubCache()
    {
        VersionManagementService::clearGitHubCache();
        
        return response()->json([
            'success' => true,
            'message' => 'GitHub cache cleared successfully'
        ]);
    }

    /**
     * Get GitHub releases
     */
    public function getGitHubReleases()
    {
        $releases = VersionManagementService::getGitHubReleases();
        
        return response()->json([
            'success' => true,
            'data' => $releases
        ]);
    }
}
