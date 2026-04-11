<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

$rows = Tenant::where('status', 'suspended')->get();
foreach ($rows as $r) {
    $r->status = 'disabled';
    // copy suspended_message to disabled_message if present
    if (empty($r->disabled_message) && !empty($r->suspended_message)) {
        $r->disabled_message = $r->suspended_message;
    }
    $r->save();
    echo "Converted: {$r->tenant_id} | {$r->business_name}\n";
}

echo "TOTAL converted: " . $rows->count() . "\n";
