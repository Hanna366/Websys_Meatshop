<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystemUpdateService
{
    public function getLatestRelease(string $owner, string $repo): array
    {
        $url = "https://api.github.com/repos/{$owner}/{$repo}/releases/latest";
        Log::info('Fetching latest release', ['url' => $url]);

        $response = Http::accept('application/vnd.github.v3+json')->get($url);

        if (! $response->successful()) {
            Log::error('Failed to fetch latest release', ['status' => $response->status()]);
            throw new \RuntimeException('Could not fetch latest release from GitHub');
        }

        $data = $response->json();

        return [
            'tag_name' => $data['tag_name'] ?? null,
            'zipball_url' => $data['zipball_url'] ?? null,
            'name' => $data['name'] ?? null,
            'body' => $data['body'] ?? null,
        ];
    }

    public function downloadReleaseZip(string $zipUrl, string $tag): string
    {
        $destDir = storage_path('app/updates');
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $zipPath = $destDir.DIRECTORY_SEPARATOR.$this->safeFilename($tag).'.zip';
        Log::info('Downloading release zip', ['url' => $zipUrl, 'dest' => $zipPath]);

        $response = Http::withOptions(['sink' => $zipPath, 'verify' => true])->get($zipUrl);

        if (! $response->successful()) {
            Log::error('Failed to download zip', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to download release zip');
        }

        return $zipPath;
    }

    public function extractZipSafe(string $zipPath, string $extractTo): string
    {
        Log::info('Extracting zip', ['zip' => $zipPath, 'to' => $extractTo]);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            Log::error('Unable to open zip archive', ['zip' => $zipPath]);
            throw new \RuntimeException('Unable to open zip archive');
        }

        if (! is_dir($extractTo)) {
            mkdir($extractTo, 0755, true);
        }

        // Prevent path traversal by rejecting entries with '..'
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (strpos($entry, '..') !== false) {
                $zip->close();
                Log::error('Zip contains invalid entry (path traversal)', ['entry' => $entry]);
                throw new \RuntimeException('Zip archive contains invalid path');
            }
        }

        // Extract
        if (! $zip->extractTo($extractTo)) {
            $zip->close();
            Log::error('Zip extraction failed', ['zip' => $zipPath]);
            throw new \RuntimeException('Zip extraction failed');
        }

        $zip->close();

        // GitHub zipballs usually contain a single top-level folder, return its path
        $children = array_values(array_filter(scandir($extractTo), function ($c) {
            return $c !== '.' && $c !== '..';
        }));

        if (count($children) === 1 && is_dir($extractTo.DIRECTORY_SEPARATOR.$children[0])) {
            return $extractTo.DIRECTORY_SEPARATOR.$children[0];
        }

        return $extractTo;
    }

    protected function safeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
    }

    public function recursiveCopy(string $source, string $dest, array $exclude = [])
    {
        $source = rtrim($source, DIRECTORY_SEPARATOR);
        $dest = rtrim($dest, DIRECTORY_SEPARATOR);

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $relPath = substr($item->getPathname(), strlen($source) + 1);

            // Check exclusions (simple prefix match)
            foreach ($exclude as $ex) {
                if ($relPath === $ex || strpos($relPath, rtrim($ex, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR) === 0) {
                    continue 2;
                }
            }

            $target = $dest.DIRECTORY_SEPARATOR.$relPath;

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                if (! is_dir(dirname($target))) {
                    mkdir(dirname($target), 0755, true);
                }
                copy($item->getPathname(), $target);
            }
        }
    }
}
