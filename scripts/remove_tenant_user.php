<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 3) {
    echo "Usage: php scripts/remove_tenant_user.php {tenant_business_name} {email}\n";
    exit(1);
}

$tenantName = $argv[1];
$email = $argv[2];

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

$tenant = Tenant::where('business_name', $tenantName)->first();
if (! $tenant) {
    echo "Tenant {$tenantName} not found\n";
    exit(2);
}

config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
DB::purge('tenant');

$deleted = DB::connection('tenant')->table('users')->where('email', $email)->delete();

if ($deleted) {
    echo "Removed {$deleted} tenant user(s) with email {$email} from tenant {$tenantName}\n";
    exit(0);
}

echo "No tenant user with email {$email} found in tenant {$tenantName}\n";
exit(0);

