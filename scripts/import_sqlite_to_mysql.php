<?php
if ($argc < 3) {
    echo "Usage: php import_sqlite_to_mysql.php path/to/sqlite.db target_mysql_db [mysql_root_password]\n";
    exit(1);
}
$sqlitePath = $argv[1];
$targetDb = $argv[2];
$rootPass = $argv[3] ?? getenv('DB_PASSWORD') ?: '';
if (!file_exists($sqlitePath)) {
    echo "File not found: $sqlitePath\n";
    exit(1);
}
try {
    $sqlite = new PDO('sqlite:' . $sqlitePath);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql = new PDO('mysql:host=127.0.0.1;port=3306', 'root', $rootPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "Creating database $targetDb if not exists...\n";
    $mysql->exec("CREATE DATABASE IF NOT EXISTS `$targetDb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysql->exec("USE `$targetDb`");

    $tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found tables: " . implode(', ', $tables) . "\n";
    foreach ($tables as $table) {
        echo "Processing table $table...\n";
        $cols = $sqlite->query("PRAGMA table_info('$table')")->fetchAll(PDO::FETCH_ASSOC);
        if (!$cols) { echo " - no columns, skipping\n"; continue; }
        $colDefs = [];
        $colNames = [];
        foreach ($cols as $c) {
            $name = $c['name'];
            $colNames[] = $name;
            $type = strtolower($c['type']);
            if (strpos($type, 'int') !== false) {
                $ctype = 'BIGINT';
            } elseif (strpos($type, 'char') !== false || strpos($type, 'text') !== false || $type === '') {
                $ctype = 'LONGTEXT';
            } elseif (strpos($type, 'blob') !== false) {
                $ctype = 'LONGBLOB';
            } elseif (strpos($type, 'real') !== false || strpos($type, 'floa') !== false || strpos($type, 'doub') !== false) {
                $ctype = 'DOUBLE';
            } else {
                $ctype = 'LONGTEXT';
            }
            $null = $c['notnull'] ? 'NOT NULL' : 'NULL';
            $colDefs[] = "`$name` $ctype $null";
        }
        $createSql = "DROP TABLE IF EXISTS `$table`; CREATE TABLE `$table` (" . implode(', ', $colDefs) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        // Split because exec doesn't accept multiple statements reliably
        $mysql->exec("DROP TABLE IF EXISTS `$table`");
        $mysql->exec("CREATE TABLE `$table` (" . implode(', ', $colDefs) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Copy rows
        $sel = $sqlite->query("SELECT * FROM `$table`");
        $rows = $sel->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) { echo " - no rows\n"; continue; }
        $placeholders = '(' . implode(',', array_fill(0, count($colNames), '?')) . ')';
        $insSql = "INSERT INTO `$table` (`" . implode('`,`', $colNames) . "`) VALUES $placeholders";
        $stmt = $mysql->prepare($insSql);
        $mysql->beginTransaction();
        $count = 0;
        foreach ($rows as $r) {
            $vals = array_values($r);
            $stmt->execute($vals);
            $count++;
        }
        $mysql->commit();
        echo " - inserted $count rows into $table\n";
    }
    echo "Import complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
