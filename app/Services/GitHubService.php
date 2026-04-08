<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GitHubService
{
    /**
     * Get GitHub releases from repository
     */
    public static function getReleases(): array
    {
        $cacheKey = 'github_releases_' . env('GITHUB_REPO_OWNER', 'Hanna366') . '_' . env('GITHUB_REPO_NAME', 'Websys_Meatshop');
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                $owner = env('GITHUB_REPO_OWNER', 'Hanna366');
                $repo = env('GITHUB_REPO_NAME', 'Websys_Meatshop');
                $token = env('GITHUB_TOKEN'); // Optional: for private repos or higher rate limits
                
                $url = "https://api.github.com/repos/{$owner}/{$repo}/releases";
                
                $headers = [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'MeatShop-POS/' . config('app.version', '1.0.0'),
                ];
                
                if ($token) {
                    $headers['Authorization'] = 'token ' . $token;
                }
                
                $response = Http::timeout(30)
                    ->withHeaders($headers)
                    ->get($url);
                
                if (!$response->successful()) {
                    Log::error('GitHub API error: ' . $response->status() . ' - ' . $response->body());
                    return [];
                }
                
                $releases = $response->json();
                
                // Format releases for our system
                $formattedReleases = [];
                foreach ($releases as $release) {
                    // Skip draft releases and pre-releases unless configured otherwise
                    if ($release['draft'] || ($release['prerelease'] && !env('GITHUB_INCLUDE_PRERELEASE', false))) {
                        continue;
                    }
                    
                    $formattedReleases[] = [
                        'tag_name' => $release['tag_name'],
                        'name' => $release['name'],
                        'body' => $release['body'],
                        'published_at' => $release['published_at'],
                        'html_url' => $release['html_url'],
                        'assets' => $release['assets'],
                        'is_prerelease' => $release['prerelease'],
                        'is_latest' => $release['tag_name'] === ($releases[0]['tag_name'] ?? null),
                        'download_count' => array_sum(array_column($release['assets'], 'download_count')),
                    ];
                }
                
                return $formattedReleases;
                
            } catch (\Exception $e) {
                Log::error('Failed to fetch GitHub releases: ' . $e->getMessage());
                return [];
            }
        });
    }
    
    /**
     * Get latest release from GitHub
     */
    public static function getLatestRelease(): ?array
    {
        $releases = self::getReleases();
        return $releases[0] ?? null;
    }
    
    /**
     * Get release by tag name
     */
    public static function getReleaseByTag(string $tag): ?array
    {
        $releases = self::getReleases();
        
        foreach ($releases as $release) {
            if ($release['tag_name'] === $tag) {
                return $release;
            }
        }
        
        return null;
    }
    
    /**
     * Download release asset
     */
    public static function downloadAsset(string $downloadUrl, string $token = null): array
    {
        try {
            $headers = [
                'Accept' => 'application/octet-stream',
                'User-Agent' => 'MeatShop-POS/' . config('app.version', '1.0.0'),
            ];
            
            if ($token) {
                $headers['Authorization'] = 'token ' . $token;
            }
            
            $response = Http::timeout(300) // 5 minutes for large files
                ->withHeaders($headers)
                ->get($downloadUrl);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Download failed: ' . $response->status(),
                ];
            }
            
            return [
                'success' => true,
                'content' => $response->body(),
                'size' => strlen($response->body()),
                'content_type' => $response->header('Content-Type'),
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to download GitHub asset: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Compare local version with latest GitHub release
     */
    public static function compareVersions(): array
    {
        $currentVersion = config('app.version', '1.0.0');
        $latestRelease = self::getLatestRelease();
        
        if (!$latestRelease) {
            return [
                'update_available' => false,
                'current_version' => $currentVersion,
                'latest_version' => $currentVersion,
                'message' => 'No releases found on GitHub',
                'github_data' => null,
            ];
        }
        
        $latestVersion = ltrim($latestRelease['tag_name'], 'v'); // Remove 'v' prefix if present
        $hasUpdate = version_compare($latestVersion, $currentVersion, '>');
        
        return [
            'update_available' => $hasUpdate,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'message' => $hasUpdate 
                ? "Update to version {$latestVersion} available on GitHub" 
                : "You are on the latest version",
            'github_data' => $latestRelease,
            'release_url' => $latestRelease['html_url'],
            'published_at' => $latestRelease['published_at'],
            'download_count' => $latestRelease['download_count'],
        ];
    }
    
    /**
     * Sync GitHub releases to local database
     */
    public static function syncReleases(): array
    {
        $releases = self::getReleases();
        $synced = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($releases as $release) {
            try {
                $version = \App\Models\Version::firstOrCreate(
                    ['version' => ltrim($release['tag_name'], 'v')],
                    [
                        'release_name' => $release['name'],
                        'description' => $release['body'],
                        'type' => self::determineReleaseType($release['tag_name'], $release['body']),
                        'status' => $release['is_prerelease'] ? 'testing' : 'stable',
                        'release_date' => $release['published_at'],
                        'features' => self::extractFeatures($release['body']),
                        'fixes' => self::extractFixes($release['body']),
                        'download_url' => self::getPrimaryDownloadUrl($release['assets']),
                        'checksum' => null, // Will be set when downloading
                        'is_mandatory' => false,
                        'auto_update' => false,
                    ]
                );
                
                if ($version->wasRecentlyCreated) {
                    $synced++;
                } else {
                    $updated++;
                }
                
            } catch (\Exception $e) {
                $errors[] = "Failed to sync release {$release['tag_name']}: " . $e->getMessage();
                Log::error("Failed to sync GitHub release: " . $e->getMessage());
            }
        }
        
        return [
            'success' => empty($errors),
            'synced' => $synced,
            'updated' => $updated,
            'errors' => $errors,
            'total_releases' => count($releases),
        ];
    }
    
    /**
     * Determine release type from tag and description
     */
    private static function determineReleaseType(string $tag, string $description): string
    {
        $version = ltrim($tag, 'v');
        $parts = explode('.', $version);
        
        if (count($parts) >= 3) {
            $major = (int)($parts[0] ?? 0);
            $minor = (int)($parts[1] ?? 0);
            $patch = (int)($parts[2] ?? 0);
            
            if ($major > 1) {
                return 'major';
            } elseif ($minor > 0 && $patch === 0) {
                return 'minor';
            } elseif ($patch > 0) {
                return 'patch';
            }
        }
        
        // Check description for hints
        $descriptionLower = strtolower($description);
        
        if (strpos($descriptionLower, 'hotfix') !== false || strpos($descriptionLower, 'urgent') !== false) {
            return 'hotfix';
        } elseif (strpos($descriptionLower, 'breaking') !== false || strpos($descriptionLower, 'major') !== false) {
            return 'major';
        } elseif (strpos($descriptionLower, 'feature') !== false || strpos($descriptionLower, 'enhancement') !== false) {
            return 'minor';
        }
        
        return 'patch';
    }
    
    /**
     * Extract features from release description
     */
    private static function extractFeatures(string $description): array
    {
        $features = [];
        $lines = explode("\n", $description);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for feature indicators
            if (preg_match('/^(?:\*|\-|\+|\s*New|Added|Feature)/i', $line)) {
                // Remove markdown and clean up
                $feature = preg_replace('/^(\*|\-|\+|\s*New|Added|Feature)\s*/i', '', $line);
                $feature = trim($feature, " \t\n\r\0\x0B-");
                
                if (!empty($feature) && strlen($feature) > 3) {
                    $features[] = $feature;
                }
            }
        }
        
        return array_slice($features, 0, 10); // Limit to 10 features
    }
    
    /**
     * Extract fixes from release description
     */
    private static function extractFixes(string $description): array
    {
        $fixes = [];
        $lines = explode("\n", $description);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for fix indicators
            if (preg_match('/^(?:\*|\-|\+|\s*Fixed|Fix|Bug)/i', $line)) {
                // Remove markdown and clean up
                $fix = preg_replace('/^(\*|\-|\+|\s*Fixed|Fix|Bug)\s*/i', '', $line);
                $fix = trim($fix, " \t\n\r\0\x0B-");
                
                if (!empty($fix) && strlen($fix) > 3) {
                    $fixes[] = $fix;
                }
            }
        }
        
        return array_slice($fixes, 0, 10); // Limit to 10 fixes
    }
    
    /**
     * Get primary download URL from assets
     */
    private static function getPrimaryDownloadUrl(array $assets): ?string
    {
        if (empty($assets)) {
            return null;
        }
        
        // Look for ZIP file first
        foreach ($assets as $asset) {
            if (strpos(strtolower($asset['name']), '.zip') !== false) {
                return $asset['browser_download_url'];
            }
        }
        
        // Return first asset if no ZIP found
        return $assets[0]['browser_download_url'] ?? null;
    }
    
    /**
     * Clear GitHub releases cache
     */
    public static function clearCache(): void
    {
        $cacheKey = 'github_releases_' . env('GITHUB_REPO_OWNER', 'Hanna366') . '_' . env('GITHUB_REPO_NAME', 'Websys_Meatshop');
        Cache::forget($cacheKey);
    }
}
