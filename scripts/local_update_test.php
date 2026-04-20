<?php
// Standalone script to create a test ZIP, extract safely, run update.php, and report.

chdir(__DIR__ . '/..'); // project root

$storageUpdates = __DIR__ . '/../storage/app/updates';
if (!is_dir($storageUpdates)) {
    @mkdir($storageUpdates, 0755, true);
}

$version = '1.2.3-test';
$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'simulate_update_' . bin2hex(random_bytes(4));
@mkdir($tmpDir, 0755, true);

// create test files
file_put_contents($tmpDir . '/README_UPDATE.txt', "This is a simulated update for version {$version}\n");

$updatePhp = <<<'PHP'
<?php
function runUpdate($path, $fromVersion, $toVersion) {
    $out = "runUpdate executed: from={$fromVersion}, to={$toVersion}\n";
    file_put_contents($path . '/update-run.log', $out, FILE_APPEND);
    return ['success' => true, 'message' => 'simulated'];
}
PHP;
file_put_contents($tmpDir . '/update.php', $updatePhp);

$zipName = "update-{$version}.zip";
$zipPath = $storageUpdates . DIRECTORY_SEPARATOR . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "Failed to create zip at {$zipPath}\n";
    exit(1);
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpDir));
foreach ($files as $file) {
    if ($file->isFile()) {
        $filePath = $file->getRealPath();
        $localName = substr($filePath, strlen($tmpDir) + 1);
        $zip->addFile($filePath, $localName);
    }
}
$zip->close();

echo "Created test zip: {$zipPath}\n";

// Safe extract
$extractTo = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'simulate_update_extract_' . bin2hex(random_bytes(4));
@mkdir($extractTo, 0755, true);

$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) {
    echo "Failed to open zip for extraction\n";
    exit(1);
}

for ($i = 0; $i < $zip->numFiles; $i++) {
    $entry = $zip->getNameIndex($i);
    if (strpos($entry, '..') !== false) {
        echo "Zip contains invalid entry (path traversal): {$entry}\n";
        $zip->close();
        exit(1);
    }
}

if (! $zip->extractTo($extractTo)) {
    echo "Extraction failed\n";
    $zip->close();
    exit(1);
}
$zip->close();

echo "Extracted to: {$extractTo}\n";

// locate update.php and run runUpdate
$found = false;
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractTo));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getBasename() === 'update.php') {
        $found = $f->getRealPath();
        break;
    }
}

if ($found) {
    echo "Found update script: {$found}\n";
    require_once $found;
    if (function_exists('runUpdate')) {
        $res = runUpdate($extractTo, '1.0.0', $version);
        echo "runUpdate returned: " . json_encode($res) . "\n";
    } else {
        echo "runUpdate function not present\n";
    }
} else {
    echo "No update.php found in extracted package\n";
}

// show update-run.log if exists
$logFile = $extractTo . DIRECTORY_SEPARATOR . 'update-run.log';
if (file_exists($logFile)) {
    echo "Contents of update-run.log:\n";
    echo file_get_contents($logFile) . "\n";
} else {
    echo "No update-run.log produced.\n";
}

// cleanup extracted files but keep zip
// Note: don't remove zip to allow user inspection
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object === '.' || $object === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $object;
        if (is_dir($path)) rrmdir($path);
        else @unlink($path);
    }
    @rmdir($dir);
}

rrmdir($extractTo);
rrmdir($tmpDir);

echo "Simulation complete. ZIP left at: {$zipPath}\n";
