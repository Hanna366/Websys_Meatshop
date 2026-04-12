<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$business = $argv[1] ?? '';
if ($business === '') {
    echo "Usage: php scripts/check_tenant_table.php {business_name}\n";
    exit(1);
}

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenant = Tenant::where('business_name', $business)->first();
if (! $tenant) {
    echo "Tenant not found: {$business}\n";
    exit(1);
}

config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
DB::purge('tenant');

$has = Schema::connection('tenant')->hasTable('password_reset_tokens') ? 'yes' : 'no';
$db = (string) (config('database.connections.tenant.database') ?? '');

echo "Tenant: {$business}\n";
echo "Database: {$db}\n";
echo "password_reset_tokens? -> {$has}\n";
