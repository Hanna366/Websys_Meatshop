<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 4) {
    echo "Usage: php scripts/perform_reset.php {tenant_business_name} {email} {plain_token} [new_password]\n";
    exit(1);
}

$business = $argv[1];
$email = $argv[2];
$plain = $argv[3];
$newPassword = $argv[4] ?? 'NewPass!234';

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Str;

$tenant = Tenant::where('business_name', $business)->first();
if (! $tenant) {
    echo "Tenant not found: {$business}\n";
    exit(1);
}

config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
DB::purge('tenant');

$row = DB::connection('tenant')->table('password_reset_tokens')->where('email', $email)->first();
if (! $row) {
    echo "No token row for {$email} in tenant DB\n";
    exit(1);
}

$dbHash = $row->token ?? null;
$enc = $row->token_encrypted ?? null;

$valid = false;
if ($dbHash && Hash::check($plain, $dbHash)) {
    $valid = true;
}

if (! $valid && $enc) {
    try {
        $decrypted = decrypt($enc);
        if ($decrypted === $plain) {
            $valid = true;
        }
    } catch (\Throwable $e) {
        echo "Decrypt failed: " . $e->getMessage() . "\n";
    }
}

if (! $valid) {
    echo "Token did not validate.\n";
    exit(1);
}

// Find user in tenant DB

$user = User::on('tenant')->where('email', $email)->first();
if (! $user) {
    // Try to find central user and copy into tenant DB
    $central = User::where('email', $email)->first();
    if ($central) {
        $now = now();
                    $tenantValues = [
                        'tenant_id' => $tenant->tenant_id,
            'username' => $central->username ?? (string) Str::slug($central->email, '_'),
            'name' => $central->name ?? '',
            'email' => $central->email,
            'password' => Hash::make($newPassword),
            'role' => $central->role ?? 'user',
            'profile' => is_null($central->profile) ? json_encode(new stdClass()) : (is_string($central->profile) ? $central->profile : json_encode($central->profile)),
            'permissions' => is_null($central->permissions) ? json_encode(new stdClass()) : (is_string($central->permissions) ? $central->permissions : json_encode($central->permissions)),
            'preferences' => is_null($central->preferences) ? null : (is_string($central->preferences) ? $central->preferences : json_encode($central->preferences)),
            'last_login' => null,
            'login_attempts' => 0,
            'lock_until' => null,
            'status' => $central->status ?? 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::connection('tenant')->table('users')->updateOrInsert(
            ['email' => $central->email],
            $tenantValues
        );

        $user = User::on('tenant')->where('email', $email)->first();
    }

    if (! $user) {
        echo "No user found for {$email} on tenant DB and no central user to copy.\n";
        exit(1);
    }
}

$user->password = Hash::make($newPassword);
$user->login_attempts = 0;
$user->lock_until = null;
$user->save();

DB::connection('tenant')->table('password_reset_tokens')->where('email', $email)->delete();

echo "Password reset performed for {$email} on tenant {$business}. New password: {$newPassword}\n";

