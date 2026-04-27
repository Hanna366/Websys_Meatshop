<?php
$path = $argv[1] ?? 'database/tenants/tenant_0c3565cd142b.sqlite';
if (!file_exists($path)) { echo "Not found: $path\n"; exit(1); }
try {
    $pdo = new PDO('sqlite:' . $path);
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in $path:\n";
    foreach ($tables as $t) echo " - $t\n";
    echo "\nSample data (first 5 rows) from some common tables:\n";
    $candidates = ['users','customers','products','tenants','settings'];
    foreach ($candidates as $table) {
        if (in_array($table, $tables)) {
            echo "Table: $table\n";
            $q = $pdo->query("SELECT * FROM $table LIMIT 5");
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                echo json_encode($r) . "\n";
            }
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
