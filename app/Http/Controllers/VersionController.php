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
        // Accept multi-line textarea fallbacks for features/fixes (features_text / fixes_text)
        if ($request->filled('features_text') && ! $request->has('features')) {
            $features = array_filter(array_map('trim', preg_split('/\r?\n/', (string) $request->input('features_text'))));
            $request->merge(['features' => $features]);
        }

        if ($request->filled('fixes_text') && ! $request->has('fixes')) {
            $fixes = array_filter(array_map('trim', preg_split('/\r?\n/', (string) $request->input('fixes_text'))));
            $request->merge(['fixes' => $fixes]);
        }

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
            'is_stable' => 'boolean',
            'is_available_to_tenants' => 'boolean',
            'is_deprecated' => 'boolean',
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
        // Accept multi-line textarea fallbacks for features/fixes when JS is disabled
        if ($request->filled('features_text') && ! $request->has('features')) {
            $features = array_filter(array_map('trim', preg_split('/\r?\n/', (string) $request->input('features_text'))));
            $request->merge(['features' => $features]);
        }

        if ($request->filled('fixes_text') && ! $request->has('fixes')) {
            $fixes = array_filter(array_map('trim', preg_split('/\r?\n/', (string) $request->input('fixes_text'))));
            $request->merge(['fixes' => $fixes]);
        }

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
            'is_stable' => 'boolean',
            'is_available_to_tenants' => 'boolean',
            'is_deprecated' => 'boolean',
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

        // Always return success true for UI convenience, include detailed result
        $message = 'Sync completed';
        if (!empty($result['errors'])) {
            $message = 'Sync completed with errors: ' . implode('; ', $result['errors']);
        } elseif (empty($result['total_releases'])) {
            $message = 'No releases found on GitHub';
        } else {
            $message = "Successfully synced {$result['total_releases']} releases. {$result['synced']} new, {$result['updated']} updated.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
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

    /**
     * List available update files in storage/app/updates for admin selection
     */
    public function listUpdateFiles()
    {
        $dir = storage_path('app/updates');
        $files = [];

        if (is_dir($dir)) {
            $items = scandir($dir);
            foreach ($items as $it) {
                if ($it === '.' || $it === '..') continue;
                $full = $dir . DIRECTORY_SEPARATOR . $it;
                if (is_file($full)) {
                    $files[] = [
                        'name' => $it,
                        'path' => 'updates/' . $it,
                        'size' => filesize($full)
                    ];
                }
            }
        }

        return response()->json(['success' => true, 'files' => $files]);
    }

    /**
     * Run a safe local simulation script to exercise the installer (admin-only)
     */
    public function simulateUpdate()
    {
        // Build command to run the standalone script with the same PHP binary
        $script = base_path('scripts/local_update_test.php');
        $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script) . ' 2>&1';

        $output = null;
        $returnVar = null;
        // Use shell_exec for simplicity; this runs the script outside Laravel boot
        $output = shell_exec($cmd);

        if ($output === null) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to execute simulation script. Check server permissions.'
            ], 500);
        }

        return response($output, 200)
            ->header('Content-Type', 'text/plain');
    }
}
