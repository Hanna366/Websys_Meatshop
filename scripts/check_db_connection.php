<?php
// Quick DB connectivity test using mysqli
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'meatshop_pos';
$port = 3306;

echo "START\n";
flush();

$m = @new mysqli($host, $user, $pass, $db, $port);
if ($m->connect_errno) {
    echo 'ERROR: ' . $m->connect_error . PHP_EOL;
    exit(1);
}

echo 'OK: connected to MySQL' . PHP_EOL;
$m->close();
