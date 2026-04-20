<?php

namespace App\Services;

use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\RunSystemUpdate;

class UpdateService
{
    protected GitHubService $gh;

    public function __construct(GitHubService $gh)
    {
        $this->gh = $gh;
    }

    public function downloadAndQueueLatest(array $options = []): array
    {
        $releases = $this->gh->fetchReleases();
        if (empty($releases)) {
            return ['success' => false, 'error' => 'No releases found from GitHub'];
        }

        $rel = $this->resolveLatestRelease($releases);
        if (! $rel) {
            return ['success' => false, 'error' => 'No valid release found from GitHub'];
        }

        $download = $this->resolveDownloadSource($rel);
        if (! $download['url']) {
            return ['success' => false, 'error' => 'No downloadable ZIP source found on the latest release'];
        }

        $version = ltrim((string) ($rel['tag_name'] ?? ''), 'v');
        $fileName = 'updates/' . Str::slug($version ?: 'latest-release') . '-' . time() . '.zip';
        $normalizedOptions = $this->normalizeOptions($options);

        try {
            $res = $this->gh->client()->get($download['url']);
            if (!$res->ok()) {
                return ['success' => false, 'error' => 'Failed to download release package'];
            }

            Storage::put($fileName, $res->body());

            $su = SystemUpdate::create([
                'version' => $version ?: null,
                'source' => 'github',
                'status' => 'pending',
                'meta' => [
                    'release' => [
                        'tag_name' => $rel['tag_name'] ?? null,
                        'name' => $rel['name'] ?? null,
                        'body' => $rel['body'] ?? null,
                        'published_at' => $rel['published_at'] ?? null,
                        'html_url' => $rel['html_url'] ?? null,
                    ],
                    'file_path' => $fileName,
                    'download' => $download,
                    'options' => $normalizedOptions,
                ],
            ]);

            RunSystemUpdate::dispatch($su->id, $fileName)->onQueue(config('queue.connections.database.queue', 'default'));

            return [
                'success' => true,
                'update_id' => $su->id,
                'version' => $version,
                'download_name' => $download['name'],
                'options' => $normalizedOptions,
            ];
        } catch (\Throwable $e) {
            Log::error('UpdateService download error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function resolveLatestRelease(array $releases): ?array
    {
        $includePrerelease = (bool) env('GITHUB_INCLUDE_PRERELEASE', false);

        foreach ($releases as $release) {
            if (! empty($release['draft'])) {
                continue;
            }

            if (! $includePrerelease && ! empty($release['prerelease'])) {
                continue;
            }

            return $release;
        }

        foreach ($releases as $release) {
            if (empty($release['draft'])) {
                return $release;
            }
        }

        return null;
    }

    protected function resolveDownloadSource(array $release): array
    {
        foreach (($release['assets'] ?? []) as $asset) {
            $name = strtolower((string) ($asset['name'] ?? ''));
            if (str_ends_with($name, '.zip') && ! empty($asset['browser_download_url'])) {
                return [
                    'url' => $asset['browser_download_url'],
                    'name' => $asset['name'],
                    'type' => 'asset',
                ];
            }
        }

        if (! empty($release['zipball_url'])) {
            return [
                'url' => $release['zipball_url'],
                'name' => ($release['tag_name'] ?? 'latest') . '.zip',
                'type' => 'zipball',
            ];
        }

        return [
            'url' => null,
            'name' => null,
            'type' => null,
        ];
    }

    protected function normalizeOptions(array $options): array
    {
        return [
            'regenerate_app_key' => (bool) ($options['regenerate_app_key'] ?? false),
            'run_composer_install' => (bool) ($options['run_composer_install'] ?? true),
            'run_npm_install' => (bool) ($options['run_npm_install'] ?? true),
            'run_migrations' => (bool) ($options['run_migrations'] ?? true),
        ];
    }
}
