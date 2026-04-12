<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 4) {
    echo "Usage: php scripts/verify_reset_token.php {tenant_business_name} {email} {plain_token}\n";
    exit(1);
}

$business = $argv[1];
$email = $argv[2];
$plain = $argv[3];

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

$row = DB::connection('tenant')->table('password_reset_tokens')->where('email', $email)->first();
if (! $row) {
    echo "No token row for {$email}\n";
    exit(1);
}

$enc = $row->token_encrypted ?? null;
if (! $enc) {
    echo "No encrypted token present in tenant row.\n";
    exit(1);
}

try {
    $decrypted = decrypt($enc);
    if ($decrypted === $plain) {
        echo "Decrypted token matches the given plain token. OK.\n";
    } else {
        echo "Decrypted token DOES NOT match given plain token.\n";
    }
} catch (\Throwable $e) {
    echo "Failed to decrypt: " . $e->getMessage() . "\n";
}


