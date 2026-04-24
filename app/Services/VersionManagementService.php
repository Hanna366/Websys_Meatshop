<?php

namespace App\Services;

use App\Models\Version;
use App\Models\UpdateLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VersionManagementService
{
    /**
     * Get current application version
     */
    public static function getCurrentVersion(): string
    {
        return config('app.version', '1.0.0');
    }

    /**
     * Check for available updates (enhanced with GitHub integration)
     */
    public static function checkForUpdates(): array
    {
        $currentVersion = self::getCurrentVersion();
        
        // First check GitHub for updates
        $githubComparison = \App\Services\GitHubService::compareVersions();
        
        if ($githubComparison['update_available']) {
            return [
                'update_available' => true,
                'current_version' => $currentVersion,
                'latest_version' => $githubComparison['latest_version'],
                'update_info' => [
                    'version' => $githubComparison['latest_version'] ?? null,
                    'type' => self::determineUpdateType($currentVersion, $githubComparison['latest_version'] ?? $currentVersion),
                    'release_name' => $githubComparison['github_data']['name'] ?? null,
                    'description' => $githubComparison['github_data']['body'] ?? null,
                    'published_at' => $githubComparison['published_at'] ?? null,
                    'release_url' => $githubComparison['release_url'] ?? null,
                    'download_count' => $githubComparison['download_count'] ?? null,
                    'source' => 'github',
                    'features' => \App\Services\GitHubService::extractFeatures($githubComparison['github_data']['body'] ?? ''),
                    'fixes' => \App\Services\GitHubService::extractFixes($githubComparison['github_data']['body'] ?? ''),
                ],
                'message' => $githubComparison['message'] ?? null,
                'source' => 'github'
            ];
        }
        
        // Fallback to local database
        $latestVersion = Version::where('status', 'stable')
            ->orderBy('release_date', 'desc')
            ->first();

        if (!$latestVersion) {
            return [
                'update_available' => false,
                'current_version' => $currentVersion,
                'latest_version' => $currentVersion,
                'message' => 'No updates available',
                'source' => 'local'
            ];
        }

        $hasUpdate = version_compare($latestVersion->version, $currentVersion, '>');

        return [
            'update_available' => $hasUpdate,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion->version,
            'update_info' => $hasUpdate ? $latestVersion : null,
            'message' => $hasUpdate ? "Update to version {$latestVersion->version} available" : 'You are on the latest version',
            'source' => 'local'
        ];
    }

    /**
     * Download update package (enhanced with GitHub support)
     */
    public static function downloadUpdate(string $version, ?int $tenantId = null): array
    {
        // First try to get from GitHub
        $githubRelease = \App\Services\GitHubService::getReleaseByTag('v' . $version);
        
        if ($githubRelease) {
            return self::downloadFromGitHub($githubRelease, $version, $tenantId);
        }
        
        // Fallback to local database
        $versionInfo = Version::where('version', $version)->first();
        
        if (!$versionInfo) {
            return ['success' => false, 'error' => 'Version not found'];
        }

        // If no download_url is configured on the Version, look for a local manifest
        if (!$versionInfo->download_url) {
            $manifestPath = storage_path('app/updates/update-' . $version . '.zip.manifest.json');
            if (file_exists($manifestPath)) {
                $m = json_decode(file_get_contents($manifestPath), true);
                if (!empty($m['file_path'])) {
                    // Create update log
                    $updateLog = UpdateLog::create([
                        'tenant_id' => $tenantId,
                        'from_version' => self::getCurrentVersion(),
                        'to_version' => $version,
                        'status' => 'completed',
                        'started_at' => now(),
                        'completed_at' => now(),
                        'update_data' => ['file_path' => $m['file_path'], 'source' => 'local']
                    ]);

                    return [
                        'success' => true,
                        'file_path' => $m['file_path'],
                        'version' => $version,
                        'log_id' => $updateLog->id,
                        'source' => 'local'
                    ];
                }
            }

            // also check a releases.json aggregate manifest
            $aggregate = storage_path('app/updates/releases.json');
            if (file_exists($aggregate)) {
                $list = json_decode(file_get_contents($aggregate), true) ?: [];
                foreach ($list as $entry) {
                    if (($entry['version'] ?? '') === $version && !empty($entry['file'])) {
                        $updateLog = UpdateLog::create([
                            'tenant_id' => $tenantId,
                            'from_version' => self::getCurrentVersion(),
                            'to_version' => $version,
                            'status' => 'completed',
                            'started_at' => now(),
                            'completed_at' => now(),
                            'update_data' => ['file_path' => $entry['file'], 'source' => 'local']
                        ]);

                        return [
                            'success' => true,
                            'file_path' => $entry['file'],
                            'version' => $version,
                            'log_id' => $updateLog->id,
                            'source' => 'local'
                        ];
                    }
                }
            }

            return ['success' => false, 'error' => 'No download URL available'];
        }

        // Create update log
        $updateLog = UpdateLog::create([
            'tenant_id' => $tenantId,
            'from_version' => self::getCurrentVersion(),
            'to_version' => $version,
            'status' => 'downloading',
            'started_at' => now(),
            'update_data' => [
                'download_url' => $versionInfo->download_url,
                'checksum' => $versionInfo->checksum,
                'file_size' => null,
                'source' => 'local'
            ]
        ]);

        try {
            // Download the update package
            $response = Http::timeout(300)->get($versionInfo->download_url);
            
            if (!$response->successful()) {
                $updateLog->update([
                    'status' => 'failed',
                    'error_message' => 'Download failed: ' . $response->status(),
                    'completed_at' => now()
                ]);
                return ['success' => false, 'error' => 'Download failed'];
            }

            // Save the update package
            $fileName = "update-{$version}.zip";
            $filePath = "updates/{$fileName}";
            
            Storage::put($filePath, $response->body());

            // Verify checksum if available
            if ($versionInfo->checksum) {
                $downloadedChecksum = md5($response->body());
                if ($downloadedChecksum !== $versionInfo->checksum) {
                    Storage::delete($filePath);
                    $updateLog->update([
                        'status' => 'failed',
                        'error_message' => 'Checksum verification failed',
                        'completed_at' => now()
                    ]);
                    return ['success' => false, 'error' => 'File integrity check failed'];
                }
            }

            $updateLog->update([
                'status' => 'completed',
                'update_data' => array_merge($updateLog->update_data, [
                    'file_path' => $filePath,
                    'file_size' => strlen($response->body())
                ]),
                'completed_at' => now()
            ]);

            return [
                'success' => true,
                'file_path' => $filePath,
                'version' => $version,
                'log_id' => $updateLog->id,
                'source' => 'local'
            ];

        } catch (\Exception $e) {
            $updateLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            Log::error("Update download failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Download update from GitHub
     */
    private static function downloadFromGitHub(array $release, string $version, ?int $tenantId = null): array
    {
        // Create update log
        $updateLog = UpdateLog::create([
            'tenant_id' => $tenantId,
            'from_version' => self::getCurrentVersion(),
            'to_version' => $version,
            'status' => 'downloading',
            'started_at' => now(),
            'update_data' => [
                'github_release' => $release,
                'source' => 'github'
            ]
        ]);

        try {
            // Find the primary asset (ZIP file)
            $primaryAsset = null;
            foreach ($release['assets'] as $asset) {
                if (strpos(strtolower($asset['name']), '.zip') !== false) {
                    $primaryAsset = $asset;
                    break;
                }
            }
            
            if (!$primaryAsset) {
                throw new \Exception('No ZIP asset found in GitHub release');
            }
            
            // Download from GitHub
            $downloadResult = \App\Services\GitHubService::downloadAsset(
                $primaryAsset['browser_download_url'],
                env('GITHUB_TOKEN')
            );
            
            if (!$downloadResult['success']) {
                throw new \Exception($downloadResult['error']);
            }
            
            // Save the update package
            $fileName = "update-{$version}.zip";
            $filePath = "updates/{$fileName}";
            
            Storage::put($filePath, $downloadResult['content']);
            
            $updateLog->update([
                'status' => 'completed',
                'update_data' => array_merge($updateLog->update_data, [
                    'file_path' => $filePath,
                    'file_size' => $downloadResult['size'],
                    'asset_name' => $primaryAsset['name'],
                    'content_type' => $downloadResult['content_type']
                ]),
                'completed_at' => now()
            ]);

            return [
                'success' => true,
                'file_path' => $filePath,
                'version' => $version,
                'log_id' => $updateLog->id,
                'source' => 'github',
                'asset_name' => $primaryAsset['name'],
                'file_size' => $downloadResult['size']
            ];

        } catch (\Exception $e) {
            $updateLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            Log::error("GitHub update download failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Install update package
     */
    public static function installUpdate(string $filePath, string $version, ?int $tenantId = null): array
    {
        $updateLog = UpdateLog::create([
            'tenant_id' => $tenantId,
            'from_version' => self::getCurrentVersion(),
            'to_version' => $version,
            'status' => 'installing',
            'started_at' => now(),
            'update_data' => ['file_path' => $filePath]
        ]);

        try {
            // Extract update package
            $extractPath = storage_path("app/updates/temp-" . Str::random(8));
            
            if (!class_exists('ZipArchive')) {
                throw new \Exception('ZipArchive extension not available');
            }

            $zip = new \ZipArchive();
            $zipFile = storage_path("app/{$filePath}");

            if ($zip->open($zipFile) !== TRUE) {
                throw new \Exception('Failed to open update package');
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // Run update script if exists
            $updateScript = $extractPath . '/update.php';
            if (file_exists($updateScript)) {
                require_once $updateScript;
                
                if (function_exists('runUpdate')) {
                    $result = runUpdate($extractPath, self::getCurrentVersion(), $version);
                    
                    if (!$result['success']) {
                        throw new \Exception($result['message'] ?? 'Update script failed');
                    }
                }
            }

            // Update version in config
            self::updateVersionInConfig($version);

            // Cleanup
            self::cleanup($extractPath);
            Storage::delete($filePath);

            $updateLog->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return [
                'success' => true,
                'version' => $version,
                'message' => "Successfully updated to version {$version}"
            ];

        } catch (\Exception $e) {
            $updateLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            Log::error("Update installation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update version in configuration
     */
    private static function updateVersionInConfig(string $version): void
    {
        $configPath = config_path('app.php');
        $configContent = file_get_contents($configPath);
        
        // Update version in config
        $configContent = preg_replace(
            "/'version' => '[^']*'/",
            "'version' => '{$version}'",
            $configContent
        );
        
        file_put_contents($configPath, $configContent);
        
        // Clear config cache
        \Artisan::call('config:clear');
    }

    /**
     * Cleanup temporary files
     */
    private static function cleanup(string $path): void
    {
        if (is_dir($path)) {
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($path);
        }
    }

    /**
     * Get update history
     */
    public static function getUpdateHistory(?int $tenantId = null): array
    {
        return UpdateLog::when($tenantId, function ($query, $tenantId) {
                return $query->where('tenant_id', $tenantId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'from_version' => $log->from_version,
                    'to_version' => $log->to_version,
                    'status' => $log->status,
                    'error_message' => $log->error_message,
                    'started_at' => $log->started_at,
                    'completed_at' => $log->completed_at,
                    'duration' => $log->completed_at 
                        ? $log->completed_at->diffInMinutes($log->started_at) 
                        : null
                ];
            })
            ->toArray();
    }

    /**
     * Create new version record
     */
    public static function createVersion(array $data): Version
    {
        // Normalize release_date: treat empty string as null to avoid SQL errors
        $releaseDate = null;
        if (isset($data['release_date']) && $data['release_date'] !== '') {
            $releaseDate = $data['release_date'];
        }

        return Version::create([
            'version' => $data['version'],
            'release_name' => $data['release_name'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'status' => $data['status'] ?? 'stable',
            'release_date' => $releaseDate,
            'features' => $data['features'] ?? [],
            'fixes' => $data['fixes'] ?? [],
            'requirements' => $data['requirements'] ?? [],
            'download_url' => $data['download_url'] ?? null,
            'checksum' => $data['checksum'] ?? null,
            'is_mandatory' => $data['is_mandatory'] ?? false,
            'auto_update' => $data['auto_update'] ?? false,
        ]);
    }
    
    /**
     * Determine update type based on version comparison
     */
    private static function determineUpdateType(string $fromVersion, string $toVersion): string
    {
        $fromParts = explode('.', $fromVersion);
        $toParts = explode('.', $toVersion);
        
        $fromMajor = (int)($fromParts[0] ?? 0);
        $toMajor = (int)($toParts[0] ?? 0);
        
        if ($toMajor > $fromMajor) {
            return 'major';
        }
        
        $fromMinor = (int)($fromParts[1] ?? 0);
        $toMinor = (int)($toParts[1] ?? 0);
        
        if ($toMinor > $fromMinor) {
            return 'minor';
        }
        
        return 'patch';
    }
    
    /**
     * Sync GitHub releases to local database
     */
    public static function syncGitHubReleases(): array
    {
        return \App\Services\GitHubService::syncReleases();
    }
    
    /**
     * Get GitHub releases
     */
    public static function getGitHubReleases(): array
    {
        return \App\Services\GitHubService::getReleases();
    }
    
    /**
     * Clear GitHub cache
     */
    public static function clearGitHubCache(): void
    {
        \App\Services\GitHubService::clearCache();
    }
}
