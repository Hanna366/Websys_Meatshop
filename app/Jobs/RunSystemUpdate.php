<?php

namespace App\Jobs;

use App\Models\SystemUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RunSystemUpdate implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    protected int $updateId;
    protected ?string $filePath;

    public function __construct(int $updateId, ?string $filePath = null)
    {
        $this->updateId = $updateId;
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        $update = SystemUpdate::find($this->updateId);
        if (! $update) {
            return;
        }

        $update->update(['status' => 'running', 'started_at' => now()]);

        $path = $this->filePath ?? ($update->meta['file_path'] ?? null);
        if (! $path) {
            $this->markFailed($update, 'No file path provided');
            return;
        }

        $zipFile = storage_path('app/' . ltrim($path, '/\\'));
        if (! file_exists($zipFile)) {
            $this->markFailed($update, 'Zip file missing: ' . $zipFile);
            return;
        }

        $extractTo = storage_path('app/updates/tmp-' . uniqid('', true));
        if (! is_dir($extractTo)) {
            mkdir($extractTo, 0755, true);
        }

        $results = [];

        try {
            if (! class_exists(\ZipArchive::class)) {
                $this->markFailed($update, 'ZipArchive extension not available', $results, $extractTo);
                return;
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipFile) !== true) {
                $this->markFailed($update, 'Failed to open zip archive', $results, $extractTo);
                return;
            }

            if ($this->archiveHasUnsafePaths($zip)) {
                $zip->close();
                $this->markFailed($update, 'Zip archive contains unsafe paths', $results, $extractTo);
                return;
            }

            if (! $zip->extractTo($extractTo)) {
                $zip->close();
                $this->markFailed($update, 'Failed to extract zip archive', $results, $extractTo);
                return;
            }

            $zip->close();

            $sourceRoot = $this->resolveSourceRoot($extractTo);

            // Create a pre-update backup of current application files (excluding large/runtime dirs)
            $backupDir = storage_path('app/backups/update-'.$update->id.'-'.time());
            $backupExclusions = ['storage', 'vendor', 'node_modules', 'updates', '.git'];
            if (! is_dir($backupDir)) mkdir($backupDir, 0755, true);
            // Copy current app into backup (will overwrite any existing backup files)
            $this->recursiveCopy(base_path(), $backupDir, $backupExclusions);

            // Persist backup path to update meta so markFailed can restore if needed
            $update->update(['meta' => $this->mergeMeta($update, ['backup_path' => $backupDir])]);

            $exclusions = ['.env', 'storage', 'vendor', 'node_modules', '.git'];
            $this->recursiveCopy($sourceRoot, base_path(), $exclusions);

            $options = $this->normalizeOptions($update->meta['options'] ?? []);

            foreach ($this->buildCommands($options) as $command) {
                if (! $command['enabled']) {
                    $results[] = [
                        'label' => $command['label'],
                        'status' => 'skipped',
                        'reason' => $command['skip_reason'],
                    ];
                    continue;
                }

                $process = new Process($command['command'], base_path());
                $process->setTimeout($command['timeout']);
                $process->run();

                $result = [
                    'label' => $command['label'],
                    'command' => $this->renderCommand($command['command']),
                    'exit' => $process->getExitCode(),
                    'out' => trim($process->getOutput()),
                    'err' => trim($process->getErrorOutput()),
                ];

                $results[] = $result;

                if (! $process->isSuccessful()) {
                    Log::error('RunSystemUpdate command failed', ['command' => $command['command'], 'error' => $process->getErrorOutput()]);
                    $this->markFailed($update, 'Command failed: ' . $command['label'], $results, $extractTo);
                    return;
                }
            }

            $this->updateEnvironmentValue(base_path('.env'), 'APP_VERSION', $update->version ?: null);

            if (file_exists(base_path('artisan'))) {
                $process = new Process($this->artisanCommand(['config:clear']), base_path());
                $process->setTimeout(120);
                $process->run();

                $results[] = [
                    'label' => 'Clear config cache',
                    'command' => $this->renderCommand($this->artisanCommand(['config:clear'])),
                    'exit' => $process->getExitCode(),
                    'out' => trim($process->getOutput()),
                    'err' => trim($process->getErrorOutput()),
                ];

                if (! $process->isSuccessful()) {
                    Log::error('RunSystemUpdate command failed', ['command' => 'config:clear', 'error' => $process->getErrorOutput()]);
                    $this->markFailed($update, 'Command failed: Clear config cache', $results, $extractTo);
                    return;
                }
            }

            $update->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => 'System update installed successfully.',
                'meta' => $this->mergeMeta($update, [
                    'commands' => $results,
                    'file_path' => $path,
                    'installed_version' => $update->version,
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error('RunSystemUpdate process error: ' . $e->getMessage(), ['exception' => $e]);
            $this->markFailed($update, $e->getMessage(), $results, $extractTo);
            return;
        }

        if (file_exists($zipFile)) {
            @unlink($zipFile);
        }

        $this->rrmdir($extractTo);
    }

    protected function recursiveCopy(string $src, string $dst, array $exclusions = []): void
    {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $subPath = substr($item->getPathname(), strlen($src) + 1);
            foreach ($exclusions as $ex) {
                $normalizedExclusion = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ex), DIRECTORY_SEPARATOR);
                if ($subPath === $normalizedExclusion || str_starts_with($subPath, $normalizedExclusion . DIRECTORY_SEPARATOR)) {
                    continue 2;
                }
            }

            $target = $dst . DIRECTORY_SEPARATOR . $subPath;
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

    protected function archiveHasUnsafePaths(\ZipArchive $zip): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = str_replace('\\', '/', (string) $zip->getNameIndex($i));

            if ($entry === '') {
                continue;
            }

            if (str_contains($entry, '../') || str_starts_with($entry, '/') || preg_match('/^[A-Za-z]:\//', $entry)) {
                return true;
            }
        }

        return false;
    }

    protected function resolveSourceRoot(string $extractTo): string
    {
        $children = array_values(array_filter(scandir($extractTo) ?: [], function (string $entry): bool {
            return $entry !== '.' && $entry !== '..';
        }));

        if (count($children) === 1) {
            $candidate = $extractTo . DIRECTORY_SEPARATOR . $children[0];
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return $extractTo;
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

    protected function buildCommands(array $options): array
    {
        $artisanExists = file_exists(base_path('artisan'));
        $composerExists = file_exists(base_path('composer.json'));
        $npmExists = file_exists(base_path('package.json'));

        return [
            [
                'label' => 'Generate app key',
                'enabled' => $options['regenerate_app_key'] && $artisanExists,
                'skip_reason' => $options['regenerate_app_key']
                    ? 'artisan file not found'
                    : 'option disabled',
                'command' => $this->artisanCommand(['key:generate', '--force']),
                'timeout' => 120,
            ],
            [
                'label' => 'Run composer install',
                'enabled' => $options['run_composer_install'] && $composerExists,
                'skip_reason' => $options['run_composer_install']
                    ? 'composer.json not found'
                    : 'option disabled',
                'command' => [
                    $this->composerExecutable(),
                    'install',
                    '--no-interaction',
                    '--prefer-dist',
                    '--optimize-autoloader',
                ],
                'timeout' => 1800,
            ],
            [
                'label' => 'Run npm install',
                'enabled' => $options['run_npm_install'] && $npmExists,
                'skip_reason' => $options['run_npm_install']
                    ? 'package.json not found'
                    : 'option disabled',
                'command' => [$this->npmExecutable(), 'install'],
                'timeout' => 1800,
            ],
            [
                'label' => 'Run database migrations',
                'enabled' => $options['run_migrations'] && $artisanExists,
                'skip_reason' => $options['run_migrations']
                    ? 'artisan file not found'
                    : 'option disabled',
                'command' => $this->artisanCommand(['migrate', '--force']),
                'timeout' => 600,
            ],
        ];
    }

    protected function artisanCommand(array $arguments): array
    {
        return array_merge([PHP_BINARY, 'artisan'], $arguments);
    }

    protected function composerExecutable(): string
    {
        return DIRECTORY_SEPARATOR === '\\' ? 'composer.bat' : 'composer';
    }

    protected function npmExecutable(): string
    {
        return DIRECTORY_SEPARATOR === '\\' ? 'npm.cmd' : 'npm';
    }

    protected function renderCommand(array $command): string
    {
        return implode(' ', array_map(static function (string $segment): string {
            return preg_match('/\s/', $segment) ? '"' . $segment . '"' : $segment;
        }, $command));
    }

    protected function updateEnvironmentValue(string $path, string $key, ?string $value): void
    {
        if (! $value || ! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }

        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        $replacement = $key . '=' . $value;

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content, 1) ?? $content;
        } else {
            $content = rtrim($content) . PHP_EOL . $replacement . PHP_EOL;
        }

        file_put_contents($path, $content);
    }

    protected function markFailed(SystemUpdate $update, string $notes, array $results = [], ?string $cleanupDir = null): void
    {
        $update->update([
            'status' => 'failed',
            'notes' => $notes,
            'completed_at' => now(),
            'meta' => $this->mergeMeta($update, ['commands' => $results]),
        ]);

        // Attempt to restore from backup if one exists
        $meta = $update->meta ?? [];
        $backup = $meta['backup_path'] ?? null;
        if ($backup && is_dir($backup)) {
            try {
                // Restore files from backup (no exclusions) to base path
                $this->recursiveCopy($backup, base_path(), []);
                // Record rollback in notes
                $update->update(['notes' => $notes . ' -- Restored from backup: ' . $backup]);
            } catch (\Throwable $e) {
                Log::error('Failed to restore backup after update failure: ' . $e->getMessage(), ['backup' => $backup]);
            }
        }

        if ($cleanupDir) {
            $this->rrmdir($cleanupDir);
        }
    }

    /**
     * Restore from an existing backup path — wrapper kept for clarity.
     */
    protected function restoreBackup(string $backupPath): void
    {
        if (! is_dir($backupPath)) return;
        $this->recursiveCopy($backupPath, base_path(), []);
    }

    protected function mergeMeta(SystemUpdate $update, array $extra): array
    {
        return array_merge($update->meta ?? [], $extra);
    }

    protected function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
