<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Domain;
use App\Models\Tenant;

$domains = Domain::all();
if ($domains->isEmpty()) {
    echo "No domain mappings found.\n";
    exit(0);
}

foreach ($domains as $d) {
    $t = Tenant::find($d->tenant_id);
    echo "domain={$d->domain} tenant_id={$d->tenant_id} tenant_name=" . ($t->business_name ?? '(not found)') . "\n";
}

// Also try to find by caberto.localhost
$search = 'caberto.localhost';
$d = Domain::where('domain', $search)->first();
if ($d) {
    $t = Tenant::find($d->tenant_id);
    echo "\nFound mapping for {$search}: tenant_id={$d->tenant_id} tenant_name=" . ($t->business_name ?? '(not found)') . "\n";
} else {
    echo "\nNo mapping found for {$search}.\n";
}
