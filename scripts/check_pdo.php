<?php
echo "PHP CLI: " . PHP_VERSION . PHP_EOL;
try {
    $drivers = PDO::getAvailableDrivers();
    echo "PDO drivers: " . implode(', ', $drivers) . PHP_EOL;
} catch (Throwable $e) {
    echo "PDO error: " . $e->getMessage() . PHP_EOL;
}

try {
    $dbPass = getenv('DB_PASSWORD') ?: '';
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=meatshop_pos','root',$dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "DB OK: connected to ";
    echo $pdo->query('select database()')->fetchColumn() . PHP_EOL;
} catch (Exception $e) {
    echo "DB ERR: " . $e->getMessage() . PHP_EOL;
}
