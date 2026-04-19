<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$email = $argv[1] ?? 'areshanna088@gmail.com';
$plain = $argv[2] ?? 'TestPass123!';

echo "Setting test password for {$email}\n";
$hash = Hash::make($plain);

try {
    $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
    echo "Updating central ({$centralConn})...\n";
    DB::connection($centralConn)->table('users')->where('email', $email)->update(['password' => $hash]);
    echo "Central updated.\n";
} catch (Throwable $e) {
    echo "Central update error: " . $e->getMessage() . "\n";
}

try {
    echo "Attempting tenant update (connection 'tenant')...\n";
    DB::connection('tenant')->table('users')->where('email', $email)->update(['password' => $hash]);
    echo "Tenant updated.\n";
} catch (Throwable $e) {
    echo "Tenant update skipped/error: " . $e->getMessage() . "\n";
}

echo "Done. New password: {$plain}\n";
