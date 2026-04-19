<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$email = $argv[1] ?? 'areshanna088@gmail.com';
echo "Checking password hashes for: {$email}\n";

try {
    $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
    echo "Central connection: {$centralConn}\n";
    $central = User::on($centralConn)->where('email', $email)->first();
    echo "Central user: " . ($central ? "FOUND" : "NOT FOUND") . PHP_EOL;
    if ($central) echo "Central password hash: {$central->password}\n";
} catch (Throwable $e) {
    echo "Central lookup error: " . $e->getMessage() . "\n";
}

try {
    echo "Attempting tenant connection lookup (connection name 'tenant')\n";
    $tenant = User::on('tenant')->where('email', $email)->first();
    echo "Tenant user: " . ($tenant ? "FOUND" : "NOT FOUND") . PHP_EOL;
    if ($tenant) echo "Tenant password hash: {$tenant->password}\n";
} catch (Throwable $e) {
    echo "Tenant lookup error: " . $e->getMessage() . "\n";
}

// Also check default connection
try {
    echo "Default connection: " . config('database.default') . "\n";
    $def = User::where('email', $email)->first();
    echo "Default user: " . ($def ? "FOUND" : "NOT FOUND") . PHP_EOL;
    if ($def) echo "Default password hash: {$def->password}\n";
} catch (Throwable $e) {
    echo "Default lookup error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
