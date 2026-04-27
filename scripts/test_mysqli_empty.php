<?php
$host='127.0.0.1'; $port=3306; $user='root'; $pass=''; $db='meatshop_pos';
$timeout=5;
$m = mysqli_init();
@mysqli_options($m, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
@mysqli_options($m, MYSQLI_OPT_READ_TIMEOUT, $timeout);
$ok = @mysqli_real_connect($m, $host, $user, $pass, $db, $port);
var_dump($ok);
if (!$ok) echo 'err: ' . mysqli_connect_error() . PHP_EOL;
else echo 'ok' . PHP_EOL;
mysqli_close($m);
