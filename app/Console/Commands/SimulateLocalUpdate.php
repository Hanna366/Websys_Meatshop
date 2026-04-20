<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SimulateLocalUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:local-update {--version=1.2.3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test ZIP in storage and run the VersionManagementService::installUpdate on it (sandbox)';

    public function handle()
    {
        $version = $this->option('version') ?: '1.2.3';

        $this->info("Creating test update ZIP for version {$version}...");

        $tmpDir = storage_path('app/updates/simulate-' . Str::random(6));
        @mkdir($tmpDir, 0755, true);

        // Create a harmless test file that mimics an app file
        $testFilePath = $tmpDir . '/TEST_UPDATE.md';
        file_put_contents($testFilePath, "This is a test update for version {$version}.\nTime: " . now());

        // Optionally include an update.php script that the installer will run
        $updateScript = $tmpDir . '/update.php';
        $updatePhp = <<<'PHP'
<?php
function runUpdate($path, $fromVersion, $toVersion) {
    // This script is executed inside the sandbox during the test.
    file_put_contents($path . '/update-run.log', "runUpdate called from {$fromVersion} to {$toVersion}\n");
    return ['success' => true, 'message' => 'OK'];
}
PHP;
        file_put_contents($updateScript, $updatePhp);

        // Create ZIP
        $zipName = "update-{$version}.zip";
        $zipPath = storage_path('app/updates/' . $zipName);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            $this->error('Failed to create zip file: ' . $zipPath);
            return 1;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir));
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tmpDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        // Clean up temp dir
        foreach (glob($tmpDir . '/*') as $f) { unlink($f); }
        @rmdir($tmpDir);

        $this->info('ZIP created: ' . $zipPath);

        // Call the installer
        $this->info('Running installer on test ZIP...');

        try {
            $result = \App\Services\VersionManagementService::installUpdate('updates/' . $zipName, $version);
            if (!empty($result['success'])) {
                $this->info('Install result: SUCCESS - ' . ($result['message'] ?? 'installed'));
            } else {
                $this->error('Install result: FAILED - ' . ($result['error'] ?? 'unknown'));
            }
        } catch (\Throwable $e) {
            $this->error('Installer threw an exception: ' . $e->getMessage());
            return 1;
        }

        $this->info('Simulation finished. Check storage/app/updates and storage/logs for details.');
        return 0;
    }
}
