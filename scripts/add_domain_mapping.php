<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Domain;
use Illuminate\Support\Facades\Schema;

$domain = $argv[1] ?? 'caberto.localhost';
$business = $argv[2] ?? 'Caberto';

$t = Tenant::where('business_name', $business)->orWhere('tenant_id', $business)->first();
if (!$t) {
    echo "Tenant not found for '{$business}'\n";
    exit(1);
}

if (!Schema::hasTable('domains')) {
    echo "No domains table present.\n";
    exit(1);
}

$d = Domain::firstOrCreate(['domain' => $domain], ['tenant_id' => $t->id]);
if ($d) {
    echo "Mapped {$domain} -> tenant_id={$t->id} ({$t->business_name})\n";
} else {
    echo "Failed to map domain.\n";
}
