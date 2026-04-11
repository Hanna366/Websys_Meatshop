<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

$tenants = Tenant::all();
foreach ($tenants as $t) {
    echo $t->id . ' | ' . $t->tenant_id . ' | ' . ($t->business_name ?? '(none)') . " | status:" . ($t->status ?? '(none)') . "\n";
}
