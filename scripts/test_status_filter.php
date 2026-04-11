<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

$status = $argv[1] ?? 'disabled';
echo "Testing filter for status={$status}\n";
$query = Tenant::query();
if ($status === 'disabled') {
    // Match controller behavior: only explicit 'disabled' status
    $query->where('status', 'disabled');
} else {
    $query->where('status', $status);
}
$rows = $query->get();
foreach ($rows as $r) {
    echo sprintf("%s | %s | %s\n", $r->tenant_id, $r->business_name, $r->status);
}
echo "TOTAL: " . $rows->count() . "\n";
