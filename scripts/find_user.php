<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 2) {
    echo "Usage: php scripts/find_user.php {email}\n";
    exit(1);
}

$email = $argv[1];
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Searching for user {$email}\n";

// Central
$central = User::where('email', $email)->first();
if ($central) {
    echo "Central user found: id={$central->id} tenant_id={$central->tenant_id}\n";
} else {
    echo "No central user found\n";
}

// Tenants: check the tenant 'Jams' specifically
$tenant = Tenant::where('business_name', 'Jams')->first();
if ($tenant) {
    config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
    DB::purge('tenant');
    $tuser = User::on('tenant')->where('email', $email)->first();
    if ($tuser) {
        echo "Tenant user found (Jams): id={$tuser->id} tenant_id={$tuser->tenant_id}\n";
    } else {
        echo "No tenant user found in Jams\n";
    }
} else {
    echo "Tenant Jams not found\n";
}

