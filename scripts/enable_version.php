<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$updated = DB::table('versions')->where('version', '1.0.9')->update([
    'is_available_to_tenants' => 1,
    'is_stable' => 1,
    'updated_at' => date('Y-m-d H:i:s')
]);

echo "Updated rows: ".intval($updated)."\n";
print_r(DB::select("select * from versions where version = ?", ['1.0.9']));
