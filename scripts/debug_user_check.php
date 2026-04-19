<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\User;

if ($argc < 3) {
    echo "Usage: php scripts/debug_user_check.php <email> <host>\n";
    exit(1);
}

$email = $argv[1];
$host = strtolower($argv[2]);

echo "Checking for email={$email} host={$host}\n";

$centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));
$tenant = null;
if ($host !== '' && ! in_array($host, $centralDomains, true)) {
    if (Schema::hasTable('domains')) {
        $domain = \App\Models\Domain::where('domain', $host)->first();
        if ($domain && $domain->tenant) $tenant = $domain->tenant;
    }
    if (! $tenant && Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
        $tenant = Tenant::where('domain', $host)->first();
    }
}

echo "Resolved tenant: ";
if ($tenant) {
    echo "FOUND tenant_id={$tenant->tenant_id} domain={$tenant->domain}\n";
} else {
    echo "NONE\n";
}

$defaultUser = User::where('email', $email)->first();
if ($defaultUser) {
    echo "Central/default user found: id={$defaultUser->id} tenant_id={$defaultUser->tenant_id} conn=" . ($defaultUser->getConnectionName() ?? config('database.default')) . " updated=" . ($defaultUser->updated_at ?? '') . "\n";
} else {
    echo "Central/default user not found.\n";
}

if ($tenant) {
    config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
    DB::purge('tenant');
    $tenantUser = User::on('tenant')->where('email', $email)->first();
    if ($tenantUser) {
        echo "Tenant user found: id={$tenantUser->id} tenant_id={$tenantUser->tenant_id} conn=tenant updated={$tenantUser->updated_at}\n";
    } else {
        echo "Tenant user not found for email.\n";
    }
}

echo "Done.\n";
