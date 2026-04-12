<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 3) {
    echo "Usage: php scripts/dump_reset_tokens.php {tenant_business_name} {email}\n";
    exit(1);
}

$business = $argv[1];
$email = $argv[2];

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenant = Tenant::where('business_name', $business)->first();
if (! $tenant) {
    echo "Tenant not found: {$business}\n";
    exit(1);
}

echo "Checking for reset tokens for email: {$email}\n";

// Central connection
$centralConn = config('database.default');
$centralDb = config("database.connections.{$centralConn}.database");
$centralHas = Schema::hasTable('password_reset_tokens') ? 'yes' : 'no';

echo PHP_EOL;
echo "Central DB Connection: {$centralConn}\n";
echo "Central DB: {$centralDb}\n";
echo "password_reset_tokens exists on central? {$centralHas}\n";

if ($centralHas) {
    $rows = DB::table('password_reset_tokens')->where('email', $email)->get();
    if ($rows->isEmpty()) {
        echo "No central token rows found for {$email}\n";
    } else {
        foreach ($rows as $r) {
            $enc = property_exists($r, 'token_encrypted') ? $r->token_encrypted : null;
            echo "central -> email={$r->email} token_hash={$r->token}";
            if ($enc) echo " token_encrypted_present=1";
            echo " created_at={$r->created_at}\n";
        }
    }
}

// Tenant connection
config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
DB::purge('tenant');
$tenantDb = (string) (config('database.connections.tenant.database') ?? '');
$tenantHas = Schema::connection('tenant')->hasTable('password_reset_tokens') ? 'yes' : 'no';

echo PHP_EOL;
echo "Tenant DB Connection: tenant\n";
echo "Tenant DB: {$tenantDb}\n";
echo "password_reset_tokens exists on tenant? {$tenantHas}\n";

if ($tenantHas) {
    $rows = DB::connection('tenant')->table('password_reset_tokens')->where('email', $email)->get();
    if ($rows->isEmpty()) {
        echo "No tenant token rows found for {$email}\n";
    } else {
        foreach ($rows as $r) {
            $enc = property_exists($r, 'token_encrypted') ? $r->token_encrypted : null;
            echo "tenant -> email={$r->email} token_hash={$r->token}";
            if ($enc) echo " token_encrypted_present=1";
            echo " created_at={$r->created_at}\n";
        }
    }
}

echo PHP_EOL;