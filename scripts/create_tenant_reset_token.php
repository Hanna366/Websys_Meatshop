<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 3) {
    echo "Usage: php scripts/create_tenant_reset_token.php {tenant_business_name} {email}\n";
    exit(1);
}

$business = $argv[1];
$email = $argv[2];

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$tenant = Tenant::where('business_name', $business)->first();
if (! $tenant) {
    echo "Tenant not found: {$business}\n";
    exit(1);
}

$plain = bin2hex(random_bytes(32));
$hashed = Hash::make($plain);
$now = date('Y-m-d H:i:s');

// Insert into central
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $email],
    (function() use ($hashed, $now) {
        $values = ['token' => $hashed, 'created_at' => $now];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('password_reset_tokens') && \Illuminate\Support\Facades\Schema::hasColumn('password_reset_tokens', 'token_encrypted')) {
                $values['token_encrypted'] = encrypt($plain);
            }
        } catch (\Throwable $e) {
            // ignore schema errors
        }
        return $values;
    })()
);

// Insert into tenant
config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
DB::purge('tenant');
DB::connection('tenant')->table('password_reset_tokens')->updateOrInsert(
    ['email' => $email],
    (function() use ($hashed, $now, $plain) {
        $values = ['token' => $hashed, 'created_at' => $now];
        try {
            if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('password_reset_tokens') && \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('password_reset_tokens', 'token_encrypted')) {
                $values['token_encrypted'] = encrypt($plain);
            }
        } catch (\Throwable $e) {
            // ignore schema errors
        }
        return $values;
    })()
);

echo "Created reset token for {$email} (tenant: {$business})\n";
echo "Plain token: {$plain}\n";
