<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $count = DB::table('tenants')->count();
    echo "TENANTS_COUNT:" . $count . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR:" . $e->getMessage() . PHP_EOL;
}
