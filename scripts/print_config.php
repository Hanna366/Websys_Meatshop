<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "APP_ENV=".config('app.env').PHP_EOL;
echo "DB_CONNECTION=".config('database.default').PHP_EOL;
print_r(config('database.connections')['mysql'] ?? []);
