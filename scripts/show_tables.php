<?php
$db = $argv[1] ?? die("Usage: php show_tables.php <db>\n");
$pass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$db", 'root', $pass);
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo $t."\n";
}
