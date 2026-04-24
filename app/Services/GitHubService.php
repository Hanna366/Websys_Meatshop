<?php
// cleaned: single-class implementation

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GitHubService
{
    protected string $owner;
    protected string $repo;
    protected ?string $token;

    public function __construct()
    {
        $repoFull = env('GITHUB_REPOSITORY', 'Hanna366/Websys_Meatshop');
        $parts = explode('/', $repoFull);
        $this->owner = env('GITHUB_REPO_OWNER') ?: ($parts[0] ?? 'Hanna366');
        $this->repo = env('GITHUB_REPO_NAME') ?: ($parts[1] ?? ($parts[0] ?? 'Websys_Meatshop'));
        $this->token = env('GITHUB_TOKEN');
    }

    public function client()
    {
        $headers = [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'MeatShop-App/' . config('app.version', '1.0.0'),
        ];

        if ($this->token) {
            $headers['Authorization'] = 'token ' . $this->token;
        }

        return Http::withHeaders($headers)->timeout(30);
    }

    public function fetchReleases(): array
    {
        try {
            $res = $this->client()->get("https://api.github.com/repos/{$this->owner}/{$this->repo}/releases");
            if ($res->ok()) {
                return $res->json();
            }
            Log::warning('GitHub releases fetch failed', ['status' => $res->status()]);
        } catch (\Throwable $e) {
            Log::error('GitHubService::fetchReleases error: ' . $e->getMessage());
        }

        return [];
    }

    public function fetchReleaseByTag(string $tag): ?array
    {
        try {
            $res = $this->client()->get("https://api.github.com/repos/{$this->owner}/{$this->repo}/releases/tags/" . rawurlencode($tag));
            if ($res->ok()) {
                return $res->json();
            }
        } catch (\Throwable $e) {
            Log::error('GitHubService::fetchReleaseByTag error: ' . $e->getMessage());
        }

        return null;
    }

    /* Static helpers for compatibility */
    public static function getReleases(): array
    {
        $svc = new self();
        $cacheKey = 'github_releases_' . $svc->owner . '_' . $svc->repo;

        return Cache::remember($cacheKey, 3600, function () use ($svc) {
            $raw = $svc->fetchReleases();
            $formatted = [];

            foreach ($raw as $release) {
                if (!empty($release['draft'])) continue;
                if (!empty($release['prerelease']) && !env('GITHUB_INCLUDE_PRERELEASE', false)) continue;

                $assets = $release['assets'] ?? [];

                $formatted[] = [
                    'tag_name' => $release['tag_name'] ?? null,
                    'name' => $release['name'] ?? null,
                    'body' => $release['body'] ?? null,
                    'published_at' => $release['published_at'] ?? null,
                    'html_url' => $release['html_url'] ?? null,
                    'assets' => $assets,
                    'is_prerelease' => $release['prerelease'] ?? false,
                    'is_latest' => false,
                    'download_count' => !empty($assets) ? array_sum(array_column($assets, 'download_count')) : 0,
                ];
            }

            if (!empty($formatted)) {
                $formatted[0]['is_latest'] = true;
                return $formatted;
            }

            // fallback to tags
            try {
                $tagsUrl = "https://api.github.com/repos/{$svc->owner}/{$svc->repo}/tags?per_page=100";
                $resp = $svc->client()->get($tagsUrl);
                if ($resp->ok()) {
                    $tags = $resp->json();
                    $out = [];
                    foreach ($tags as $t) {
                        $name = $t['name'] ?? null;
                        if (!$name) continue;
                        $out[] = [
                            'tag_name' => $name,
                            'name' => $name,
                            'body' => '',
                            'published_at' => null,
                            'html_url' => "https://github.com/{$svc->owner}/{$svc->repo}/tree/{$name}",
                            'assets' => [],
                            'is_prerelease' => false,
                            'is_latest' => false,
                            'download_count' => 0,
                        ];
                    }
                    return $out;
                }
            } catch (\Throwable $e) {
                Log::warning('GitHub tags fallback failed: ' . $e->getMessage());
            }

            return [];
        });
    }

    public static function getLatestRelease(): ?array
    {
        $rels = self::getReleases();
        return $rels[0] ?? null;
    }

    public static function getReleaseByTag(string $tag): ?array
    {
        foreach (self::getReleases() as $r) {
            if (($r['tag_name'] ?? null) === $tag) return $r;
        }
        return null;
    }

    public static function downloadAsset(string $downloadUrl, ?string $token = null): array
    {
        try {
            $headers = [
                'Accept' => 'application/octet-stream',
                'User-Agent' => 'MeatShop-App/' . config('app.version', '1.0.0'),
            ];

            if ($token) $headers['Authorization'] = 'token ' . $token;

            $resp = Http::withHeaders($headers)->timeout(300)->get($downloadUrl);
            if (!$resp->successful()) {
                return ['success' => false, 'error' => 'Download failed: ' . $resp->status()];
            }
            return ['success' => true, 'content' => $resp->body(), 'size' => strlen($resp->body()), 'content_type' => $resp->header('Content-Type')];
        } catch (\Throwable $e) {
            Log::error('GitHubService::downloadAsset error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function compareVersions(): array
    {
        $current = config('app.version', '1.0.0');
        $latest = self::getLatestRelease();
        if (!$latest) return ['update_available' => false, 'current_version' => $current, 'latest_version' => $current];
        $latestVersion = ltrim($latest['tag_name'] ?? $current, 'v');
        return ['update_available' => version_compare($latestVersion, $current, '>'), 'current_version' => $current, 'latest_version' => $latestVersion, 'github_data' => $latest];
    }

    public static function syncReleases(): array
    {
        $releases = self::getReleases();
        if (empty($releases)) {
            Log::warning('GitHubService::syncReleases - no releases');
            // Treat no remote releases as a successful sync with zero results
            return ['success' => true, 'synced' => 0, 'updated' => 0, 'errors' => [], 'total_releases' => 0];
        }

        $synced = 0; $updated = 0; $errors = [];
        foreach ($releases as $release) {
            try {
                $versionStr = ltrim($release['tag_name'] ?? '', 'v');
                if (!$versionStr) continue;
                $model = \App\Models\Version::firstOrCreate(
                    ['version' => $versionStr],
                    [
                        'release_name' => $release['name'] ?? null,
                        'description' => $release['body'] ?? null,
                        'type' => self::determineReleaseType($release['tag_name'] ?? '' , $release['body'] ?? ''),
                        'status' => !empty($release['is_prerelease']) ? 'testing' : 'stable',
                        'release_date' => $release['published_at'] ?? null,
                        'features' => self::extractFeatures($release['body'] ?? ''),
                        'fixes' => self::extractFixes($release['body'] ?? ''),
                        'download_url' => self::getPrimaryDownloadUrl($release['assets'] ?? []),
                        'checksum' => null,
                        'is_mandatory' => false,
                        'auto_update' => false,
                    ]
                );
                if ($model->wasRecentlyCreated) $synced++; else $updated++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
                Log::error('GitHubService::syncReleases error: ' . $e->getMessage());
            }
        }

        return ['success' => empty($errors), 'synced' => $synced, 'updated' => $updated, 'errors' => $errors, 'total_releases' => count($releases)];
    }

    private static function determineReleaseType(string $tag, string $description): string
    {
        $version = ltrim($tag, 'v');
        $parts = explode('.', $version);
        if (count($parts) >= 3) {
            $major = (int)($parts[0] ?? 0);
            $minor = (int)($parts[1] ?? 0);
            $patch = (int)($parts[2] ?? 0);
            if ($major > 0 && $minor === 0 && $patch === 0) return 'major';
            if ($minor > 0 && $patch === 0) return 'minor';
            if ($patch > 0) return 'patch';
        }
        $d = strtolower($description);
        if (str_contains($d, 'hotfix') || str_contains($d, 'urgent')) return 'hotfix';
        if (str_contains($d, 'breaking') || str_contains($d, 'major')) return 'major';
        if (str_contains($d, 'feature') || str_contains($d, 'enhancement')) return 'minor';
        return 'patch';
    }

    public static function extractFeatures(string $description): array
    {
        $out = [];
        $lines = preg_split('/\r?\n/', $description);
        foreach ($lines as $l) {
            $t = trim($l);
            if (preg_match('/^(?:\*|\-|\+|New|Added|Feature)/i', $t)) {
                $t = preg_replace('/^(?:\*|\-|\+|New|Added|Feature)\s*/i', '', $t);
                $t = trim($t, " \t\n\r\0\x0B-:.");
                if ($t && strlen($t) > 3) $out[] = $t;
            }
        }
        return $out;
    }

    public static function extractFixes(string $description): array
    {
        $out = [];
        $lines = preg_split('/\r?\n/', $description);
        foreach ($lines as $l) {
            $t = trim($l);
            if (preg_match('/^(?:Fix|Fixes|Fixed|Bug|Patch)/i', $t)) {
                $t = preg_replace('/^(?:Fix|Fixes|Fixed|Bug|Patch)\s*/i', '', $t);
                $t = trim($t, " \t\n\r\0\x0B-:.");
                if ($t && strlen($t) > 3) $out[] = $t;
            }
        }
        return $out;
    }

    private static function getPrimaryDownloadUrl(array $assets): ?string
    {
        if (empty($assets)) return null;
        foreach ($assets as $a) {
            if (stripos($a['name'] ?? '', '.zip') !== false) return $a['browser_download_url'] ?? null;
        }
        return $assets[0]['browser_download_url'] ?? null;
    }
}

// VSCODE_REFRESH
