<?php

try {
    $dbName = $argv[1] ?? 'meatshop_pos';
    $dbPass = getenv('DB_PASSWORD') ?: '';
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$dbName", 'root', $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    echo "OK: connected to " . $pdo->query('select database()')->fetchColumn() . PHP_EOL;
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t: $n\n";
    }
} catch (Exception $e) {
    echo "ERR: " . $e->getMessage() . PHP_EOL;
    if (method_exists($e, 'getTraceAsString')) {
        echo $e->getTraceAsString();
    }
}
