<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\VersionManagementService;
use App\Services\GitHubService;
use App\Models\Version;

class GitHubWebhookController extends Controller
{
    /**
     * Handle incoming GitHub webhooks (public endpoint)
     */
    public function handle(Request $request)
    {
        $secret = env('GITHUB_WEBHOOK_SECRET');

        $signature256 = $request->header('X-Hub-Signature-256');
        $signature = $signature256 ?: $request->header('X-Hub-Signature');
        $payload = $request->getContent();

        if ($secret && $signature) {
            if (strpos($signature, 'sha256=') === 0) {
                $computed = 'sha256=' . hash_hmac('sha256', $payload, $secret);
            } else {
                $computed = 'sha1=' . hash_hmac('sha1', $payload, $secret);
            }

            if (!hash_equals($computed, $signature)) {
                Log::warning('GitHub webhook signature mismatch', ['expected' => $computed, 'received' => $signature]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
            }
        }

        $event = $request->header('X-GitHub-Event');

        // Only act on release events for now
        if ($event === 'release') {
            $data = $request->json()->all();
            $action = $data['action'] ?? null;

            // Trigger a sync on published/created releases
            if (in_array($action, ['published', 'created', 'released'])) {
                try {
                    // Clear cached GitHub releases so we fetch the new release immediately
                    \App\Services\GitHubService::clearCache();

                    // Try to persist only the single release from payload for immediate visibility
                    $release = $data['release'] ?? null;
                    $tag = $release['tag_name'] ?? null;

                    // Prefer fetching the canonical release from GitHub API (fresh)
                    $fetched = null;
                    if ($tag) {
                        try {
                            $fetched = (new GitHubService())->fetchReleaseByTag($tag);
                        } catch (\Throwable $e) {
                            Log::warning('GitHubWebhookController fetchReleaseByTag failed: ' . $e->getMessage());
                        }
                    }

                    $payloadRelease = $fetched ?? $release;

                    if ($payloadRelease && ($payloadRelease['tag_name'] ?? null)) {
                        $versionStr = ltrim($payloadRelease['tag_name'], 'v');
                        // determine simple type by version parts
                        $parts = explode('.', $versionStr);
                        $type = 'patch';
                        if (isset($parts[0]) && isset($parts[1]) && isset($parts[2])) {
                            $major = (int)$parts[0];
                            $minor = (int)$parts[1];
                            $patch = (int)$parts[2];
                            if ($minor === 0 && $patch === 0) $type = 'major';
                            elseif ($patch === 0) $type = 'minor';
                        }

                        $isPrerelease = !empty($payloadRelease['prerelease']);
                        $status = $isPrerelease ? 'testing' : 'stable';

                        // find primary ZIP asset if present
                        $downloadUrl = null;
                        $assets = $payloadRelease['assets'] ?? [];
                        foreach ($assets as $a) {
                            if (!empty($a['browser_download_url']) && stripos($a['name'] ?? '', '.zip') !== false) {
                                $downloadUrl = $a['browser_download_url'];
                                break;
                            }
                        }

                        $model = Version::updateOrCreate(
                            ['version' => $versionStr],
                            [
                                'release_name' => $payloadRelease['name'] ?? null,
                                'description' => $payloadRelease['body'] ?? null,
                                'type' => $type,
                                'status' => $status,
                                'release_date' => $payloadRelease['published_at'] ?? null,
                                'features' => GitHubService::extractFeatures($payloadRelease['body'] ?? ''),
                                'fixes' => GitHubService::extractFixes($payloadRelease['body'] ?? ''),
                                'download_url' => $downloadUrl,
                                'checksum' => null,
                                'is_mandatory' => false,
                                'auto_update' => false,
                                'is_stable' => $status === 'stable',
                                'is_available_to_tenants' => $status === 'stable',
                                'is_deprecated' => false,
                            ]
                        );

                        Log::info('GitHubWebhookController upserted version ' . $versionStr);

                        // Also run a targeted sync for other releases if desired
                        $syncResult = VersionManagementService::syncGitHubReleases();

                        return response()->json(['success' => true, 'message' => 'Release persisted', 'version' => $model->version, 'sync' => $syncResult]);
                    }

                    // fallback to full sync
                    $result = VersionManagementService::syncGitHubReleases();
                    return response()->json(['success' => true, 'message' => 'Release sync triggered', 'data' => $result]);
                } catch (\Throwable $e) {
                    Log::error('GitHubWebhookController sync error: ' . $e->getMessage());
                    return response()->json(['success' => false, 'message' => 'Sync failed'], 500);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Event ignored']);
    }
}
