<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CreateLocalRelease extends Command
{
    protected $signature = 'release:create-local {version?} {--name=} {--desc=} {--mandatory}';
    protected $description = 'Create a local release ZIP and register a Version record (DB or fallback JSON)';

    public function handle()
    {
        $version = $this->argument('version') ?: config('app.version', '1.0.0');
        $version = ltrim($version, 'v');

        $releaseName = $this->option('name') ?? "Local release v{$version}";
        $desc = $this->option('desc') ?? "Local release created on " . now();
        $isMandatory = $this->option('mandatory') ? true : false;

        $this->info("Creating release ZIP for version {$version}...");

        $destDir = storage_path('app/updates');
        if (! is_dir($destDir)) mkdir($destDir, 0755, true);

        $zipName = "update-{$version}.zip";
        $zipPath = $destDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->error('Failed to create zip file: ' . $zipPath);
            return 1;
        }

        // Include selected folders
        $include = ['app', 'resources', 'public', 'routes', 'composer.json', 'package.json'];
        $excludePaths = ['storage', 'vendor', 'node_modules', '.env', '.git'];

        foreach ($include as $item) {
            $path = base_path($item);
            if (!file_exists($path)) continue;

            if (is_dir($path)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
                foreach ($files as $file) {
                    if (!$file->isFile()) continue;
                    $real = $file->getRealPath();
                    $rel = Str::replaceFirst(base_path() . DIRECTORY_SEPARATOR, '', $real);

                    $skip = false;
                    foreach ($excludePaths as $ex) {
                        if (str_starts_with($rel, trim($ex, DIRECTORY_SEPARATOR))) { $skip = true; break; }
                    }
                    if ($skip) continue;

                    $zip->addFile($real, $rel);
                }
            } else {
                $zip->addFile($path, basename($path));
            }
        }

        // Add a simple metadata file
        $meta = json_encode(['version' => $version, 'name' => $releaseName, 'description' => $desc, 'created_at' => now()], JSON_PRETTY_PRINT);
        $zip->addFromString('RELEASE_METADATA.json', $meta);

        $zip->close();

        $this->info('ZIP created: ' . $zipPath);

        // Attempt to create Version record in DB
        try {
            if (class_exists(\App\Models\Version::class)) {
                $ver = \App\Services\VersionManagementService::createVersion([
                    'version' => $version,
                    'release_name' => $releaseName,
                    'description' => $desc,
                    'type' => 'patch',
                    'status' => 'stable',
                    'release_date' => now()->toDateString(),
                    'features' => [],
                    'fixes' => [],
                    'requirements' => [],
                    'download_url' => null,
                    'checksum' => md5_file($zipPath),
                    'is_mandatory' => $isMandatory,
                    'auto_update' => false,
                ]);

                // store file_path in update storage for UI convenience
                Storage::put("updates/{$zipName}.manifest.json", json_encode(['file_path' => "updates/{$zipName}", 'version' => $version]));

                $this->info('Version model created: ' . ($ver->version ?? 'unknown'));
            }
        } catch (\Throwable $e) {
            $this->warn('Could not create DB record: ' . $e->getMessage());
            // fallback: write to a local releases.json
            $manifest = storage_path('app/updates/releases.json');
            $list = [];
            if (file_exists($manifest)) {
                $list = json_decode(file_get_contents($manifest), true) ?: [];
            }
            $list[] = ['version' => $version, 'name' => $releaseName, 'description' => $desc, 'file' => "updates/{$zipName}", 'checksum' => md5_file($zipPath), 'created_at' => now()];
            file_put_contents($manifest, json_encode($list, JSON_PRETTY_PRINT));
            $this->info('Wrote fallback release manifest: ' . $manifest);
        }

        $this->info('Local release creation complete.');
        return 0;
    }
}
