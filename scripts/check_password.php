<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 3) {
    echo "Usage: php scripts/check_password.php {email} {password}\n";
    exit(1);
}

$email = $argv[1];
$password = $argv[2];

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$tenant = Tenant::where('business_name', 'Jams')->first();
if (! $tenant) {
    echo "Tenant Jams not found\n";
    exit(1);
}

config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
DB::purge('tenant');

$tuser = User::on('tenant')->where('email', $email)->first();
if (! $tuser) {
    echo "Tenant user not found\n";
    exit(1);
}

$hash = $tuser->password;
echo "Tenant user id={$tuser->id} email={$tuser->email}\n";
echo "Stored hash: {$hash}\n";
$ok = Hash::check($password, $hash) ? 'MATCH' : 'NO MATCH';
echo "Password check for '{$password}': {$ok}\n";

?>