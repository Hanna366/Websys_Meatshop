<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

$app = require $basePath . '/bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

date_default_timezone_set(config('app.timezone', 'UTC'));

$options = parseArgs($argv);
$backupRoot = realpath($options['backup-root'] ?? ($basePath . '/database/tenant_corruption_backups/batch_20260424_161931'));

if ($backupRoot === false || !is_dir($backupRoot)) {
    fwrite(STDERR, "Backup root not found.\n");
    exit(1);
}

$centralConnection = (string) (config('tenancy.database.central_connection')
    ?? env('DB_CONNECTION', 'mysql'));

if ($centralConnection === 'tenant') {
    $centralConnection = (string) env('DB_CONNECTION', 'mysql');
}

$centralConfig = config("database.connections.{$centralConnection}", []);
$host = (string) ($centralConfig['host'] ?? '127.0.0.1');
$port = (string) ($centralConfig['port'] ?? '3306');
$username = (string) ($centralConfig['username'] ?? '');
$password = (string) ($centralConfig['password'] ?? '');
$centralDatabase = (string) ($centralConfig['database'] ?? 'meatshop_pos');

$pdo = new PDO(
    "mysql:host={$host};port={$port};charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$datadir = (string) ($pdo->query('SELECT @@datadir')->fetchColumn() ?: '');
if ($datadir === '') {
    fwrite(STDERR, "Unable to resolve MySQL datadir.\n");
    exit(1);
}

$schemaFilter = isset($options['schema']) ? array_filter(explode(',', (string) $options['schema'])) : null;
$includeOrphans = array_key_exists('include-orphans', $options);

$knownSchemas = fetchKnownCentralSchemas($pdo, $centralDatabase);
$schemaDirs = collectSchemaDirectories($backupRoot);

$schemasToRestore = [];
foreach ($schemaDirs as $schema => $dir) {
    if ($schemaFilter !== null && !in_array($schema, $schemaFilter, true)) {
        continue;
    }

    $isKnown = in_array($schema, $knownSchemas, true);
    if (!$isKnown && !$includeOrphans) {
        continue;
    }

    $schemasToRestore[$schema] = $dir;
}

if ($schemaFilter !== null) {
    foreach ($schemaFilter as $schema) {
        if (!isset($schemasToRestore[$schema]) && isset($schemaDirs[$schema])) {
            $schemasToRestore[$schema] = $schemaDirs[$schema];
        }
    }
}

if ($schemasToRestore === []) {
    fwrite(STDOUT, "No schemas selected for restore.\n");
    exit(0);
}

$restoreLog = [];
$tablePreference = [
    'migrations',
    'users',
    'customers',
    'suppliers',
    'products',
    'categories',
    'product_categories',
    'units_of_measure',
    'price_lists',
    'product_prices',
    'price_list_items',
    'sales',
    'inventory_batches',
    'permissions',
    'roles',
    'role_has_permissions',
    'model_has_permissions',
    'model_has_roles',
    'password_reset_tokens',
];

foreach ($schemasToRestore as $schema => $dir) {
    line("=== Restoring {$schema} ===");

    recreateDatabase($pdo, $schema);
    runTenantMigrationsForSchema($schema, $centralConfig);

    $tables = collectBackupTables($dir, $tablePreference);
    if ($tables === []) {
        $restoreLog[$schema] = [
            'status' => 'skipped',
            'reason' => 'no .ibd files found',
        ];
        line("No tables found in backup folder for {$schema}.");
        continue;
    }

    $tablePlans = [];
    foreach ($tables as $table) {
        $createSql = fetchCreateTable($pdo, $schema, $table);
        if ($createSql === null) {
            $tablePlans[$table] = [
                'status' => 'skipped',
                'reason' => 'table missing after migrations',
            ];
            continue;
        }

        $plan = buildTablePlan($createSql);
        if ($plan === null) {
            $tablePlans[$table] = [
                'status' => 'skipped',
                'reason' => 'unable to parse CREATE TABLE',
            ];
            continue;
        }

        $tablePlans[$table] = $plan;
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    foreach ($tablePlans as $table => $plan) {
        if (($plan['status'] ?? null) === 'skipped') {
            line("Skipping {$schema}.{$table}: {$plan['reason']}");
            continue;
        }

        $sourceIbd = $dir . DIRECTORY_SEPARATOR . $table . '.ibd';
        $targetDir = rtrim($datadir, '\\/') . DIRECTORY_SEPARATOR . $schema;
        $targetIbd = $targetDir . DIRECTORY_SEPARATOR . $table . '.ibd';

        try {
            $pdo->exec("DROP TABLE IF EXISTS `{$schema}`.`{$table}`");
            $pdo->exec(qualifyCreateTable($plan['minimal_create'], $schema, $table));
            $pdo->exec("ALTER TABLE `{$schema}`.`{$table}` DISCARD TABLESPACE");

            if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                throw new RuntimeException("Unable to create target dir {$targetDir}");
            }

            if (!copy($sourceIbd, $targetIbd)) {
                throw new RuntimeException("Failed to copy {$sourceIbd} to {$targetIbd}");
            }

            $pdo->exec("ALTER TABLE `{$schema}`.`{$table}` IMPORT TABLESPACE");

            $tablePlans[$table]['status'] = 'imported';
            line("Imported {$schema}.{$table}");
        } catch (Throwable $e) {
            $tablePlans[$table]['status'] = 'failed';
            $tablePlans[$table]['reason'] = $e->getMessage();
            line("Failed {$schema}.{$table}: {$e->getMessage()}");
        }
    }

    foreach ($tablePlans as $table => $plan) {
        if (($plan['status'] ?? null) !== 'imported') {
            continue;
        }

        try {
            foreach ($plan['index_alters'] as $statement) {
                $pdo->exec(qualifyAlterTable($statement, $schema, $table));
            }

            foreach ($plan['constraint_alters'] as $statement) {
                $pdo->exec(qualifyAlterTable($statement, $schema, $table));
            }

            $tablePlans[$table]['status'] = 'restored';
            $tablePlans[$table]['row_count'] = fetchRowCount($pdo, $schema, $table);
            line("Rebuilt indexes for {$schema}.{$table}");
        } catch (Throwable $e) {
            $tablePlans[$table]['status'] = 'partial';
            $tablePlans[$table]['reason'] = $e->getMessage();
            $tablePlans[$table]['row_count'] = fetchRowCount($pdo, $schema, $table);
            line("Partial restore {$schema}.{$table}: {$e->getMessage()}");
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    $restoreLog[$schema] = summarizeSchema($tablePlans);
}

$timestamp = date('Ymd_His');
$reportPath = $basePath . '/database/tenant_corruption_backups/restore_report_' . $timestamp . '.json';
file_put_contents($reportPath, json_encode($restoreLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

line("Report written to {$reportPath}");
line(json_encode($restoreLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

function parseArgs(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }

        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $options[$key] = $value;
            continue;
        }

        $options[$arg] = true;
    }

    return $options;
}

function line(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

function fetchKnownCentralSchemas(PDO $pdo, string $centralDatabase): array
{
    try {
        $statement = $pdo->query(
            "SELECT db_name FROM `{$centralDatabase}`.`tenants` WHERE db_name IS NOT NULL AND db_name <> ''"
        );

        return array_values(array_filter(array_map(
            static fn (array $row): string => (string) $row['db_name'],
            $statement->fetchAll()
        )));
    } catch (Throwable $e) {
        return [];
    }
}

function collectSchemaDirectories(string $backupRoot): array
{
    $schemas = [];
    $items = scandir($backupRoot) ?: [];

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $backupRoot . DIRECTORY_SEPARATOR . $item;
        if (is_dir($fullPath) && str_starts_with($item, 'tenant_')) {
            $schemas[$item] = $fullPath;
        }
    }

    ksort($schemas);

    return $schemas;
}

function recreateDatabase(PDO $pdo, string $schema): void
{
    $safeSchema = preg_replace('/[^A-Za-z0-9_]/', '_', $schema);
    $pdo->exec("DROP DATABASE IF EXISTS `{$safeSchema}`");
    $pdo->exec("CREATE DATABASE `{$safeSchema}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}

function runTenantMigrationsForSchema(string $schema, array $centralConfig): void
{
    config([
        'database.connections.tenant' => [
            'driver' => $centralConfig['driver'] ?? 'mysql',
            'host' => $centralConfig['host'] ?? '127.0.0.1',
            'port' => $centralConfig['port'] ?? '3306',
            'database' => $schema,
            'username' => $centralConfig['username'] ?? null,
            'password' => $centralConfig['password'] ?? null,
            'charset' => $centralConfig['charset'] ?? 'utf8mb4',
            'collation' => $centralConfig['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => $centralConfig['strict'] ?? true,
            'engine' => $centralConfig['engine'] ?? null,
            'options' => $centralConfig['options'] ?? [],
        ],
    ]);

    \Illuminate\Support\Facades\DB::purge('tenant');

    Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
}

function collectBackupTables(string $schemaDir, array $preferredOrder): array
{
    $tables = [];
    $files = scandir($schemaDir) ?: [];

    foreach ($files as $file) {
        if (!str_ends_with($file, '.ibd')) {
            continue;
        }

        $tables[] = pathinfo($file, PATHINFO_FILENAME);
    }

    $tables = array_values(array_unique($tables));

    usort($tables, static function (string $a, string $b) use ($preferredOrder): int {
        $aIndex = array_search($a, $preferredOrder, true);
        $bIndex = array_search($b, $preferredOrder, true);

        $aOrder = $aIndex === false ? PHP_INT_MAX : $aIndex;
        $bOrder = $bIndex === false ? PHP_INT_MAX : $bIndex;

        if ($aOrder === $bOrder) {
            return strcmp($a, $b);
        }

        return $aOrder <=> $bOrder;
    });

    return $tables;
}

function fetchCreateTable(PDO $pdo, string $schema, string $table): ?string
{
    try {
        $statement = $pdo->query("SHOW CREATE TABLE `{$schema}`.`{$table}`");
        $row = $statement->fetch();

        return $row['Create Table'] ?? null;
    } catch (Throwable $e) {
        return null;
    }
}

function buildTablePlan(string $createSql): ?array
{
    $parts = explode("\n", $createSql);
    if (count($parts) < 3) {
        return null;
    }

    $firstLine = array_shift($parts);
    $lastLine = array_pop($parts);

    $primaryLines = [];
    $indexLines = [];
    $constraintLines = [];

    foreach ($parts as $line) {
        $trimmed = trim($line);
        $trimmed = rtrim($trimmed, ',');

        if (str_starts_with($trimmed, 'PRIMARY KEY')) {
            $primaryLines[] = $trimmed;
            continue;
        }

        if (str_starts_with($trimmed, 'UNIQUE KEY') || str_starts_with($trimmed, 'KEY')) {
            $indexLines[] = $trimmed;
            continue;
        }

        if (str_starts_with($trimmed, 'CONSTRAINT')) {
            $constraintLines[] = $trimmed;
            continue;
        }

        $primaryLines[] = $trimmed;
    }

    $body = "  " . implode(",\n  ", $primaryLines);
    $minimalCreate = $firstLine . "\n" . $body . "\n" . $lastLine;

    preg_match('/^CREATE TABLE `([^`]+)`/m', $firstLine, $matches);
    $table = $matches[1] ?? null;
    if ($table === null) {
        return null;
    }

    $indexAlters = [];
    foreach ($indexLines as $indexLine) {
        $indexAlters[] = "ALTER TABLE `{$table}` ADD {$indexLine}";
    }

    $constraintAlters = [];
    foreach ($constraintLines as $constraintLine) {
        $constraintAlters[] = "ALTER TABLE `{$table}` ADD {$constraintLine}";
    }

    return [
        'minimal_create' => $minimalCreate,
        'index_alters' => $indexAlters,
        'constraint_alters' => $constraintAlters,
    ];
}

function fetchRowCount(PDO $pdo, string $schema, string $table): int
{
    try {
        return (int) $pdo->query("SELECT COUNT(*) FROM `{$schema}`.`{$table}`")->fetchColumn();
    } catch (Throwable $e) {
        return -1;
    }
}

function summarizeSchema(array $tablePlans): array
{
    $summary = [
        'restored' => [],
        'partial' => [],
        'failed' => [],
        'skipped' => [],
    ];

    foreach ($tablePlans as $table => $plan) {
        $entry = [
            'table' => $table,
            'rows' => $plan['row_count'] ?? null,
        ];

        if (isset($plan['reason'])) {
            $entry['reason'] = $plan['reason'];
        }

        $status = $plan['status'] ?? 'skipped';
        if (!isset($summary[$status])) {
            $summary[$status] = [];
        }

        $summary[$status][] = $entry;
    }

    return $summary;
}

function qualifyCreateTable(string $sql, string $schema, string $table): string
{
    $needle = "CREATE TABLE `{$table}`";
    $replacement = "CREATE TABLE `{$schema}`.`{$table}`";

    return str_replace($needle, $replacement, $sql);
}

function qualifyAlterTable(string $sql, string $schema, string $table): string
{
    $needle = "ALTER TABLE `{$table}`";
    $replacement = "ALTER TABLE `{$schema}`.`{$table}`";

    return str_replace($needle, $replacement, $sql);
}
