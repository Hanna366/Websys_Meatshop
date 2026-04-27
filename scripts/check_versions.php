<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::select("select version, is_available_to_tenants, release_name, release_date from versions order by release_date desc limit 20");
print_r($res);

$v = DB::select("select * from versions where version = ? limit 1", ['1.0.9']);
print_r([ 'v1.0.9' => $v ]);

echo "Done\n";
