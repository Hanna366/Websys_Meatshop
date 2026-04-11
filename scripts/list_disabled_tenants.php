<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

$rows = Tenant::whereIn('status', ['disabled', 'suspended'])->get(['tenant_id','business_name','status','domain']);
foreach ($rows as $r) {
    echo sprintf("%s | %s | %s | %s\n", $r->tenant_id, $r->business_name, $r->status, $r->domain);
}

echo "TOTAL: " . $rows->count() . "\n";
