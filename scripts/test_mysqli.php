<?php
$host='127.0.0.1'; $port=3306; $user='root'; $pass='YourNewStrongPassword'; $db='meatshop_pos';
$timeout = 5;
if (!function_exists('mysqli_init')) { echo "no mysqli\n"; exit; }
$mysqli = mysqli_init();
if ($mysqli===false) { echo "init failed\n"; exit; }
mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
mysqli_options($mysqli, MYSQLI_OPT_READ_TIMEOUT, $timeout);
$connected = @mysqli_real_connect($mysqli, $host, $user, $pass, $db, $port);
echo 'connected? ' . ($connected ? 'true' : 'false') . PHP_EOL;
if (!$connected) { echo 'error: ' . mysqli_connect_error() . PHP_EOL; }
else { echo 'client info: ' . mysqli_get_client_info() . PHP_EOL; }
mysqli_close($mysqli);
