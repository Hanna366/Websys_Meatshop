<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SimpleAuthController;

if ($argc < 4) {
    echo "Usage: php scripts/do_full_reset.php <delivery_email> <login_email> <new_password> [host]\n";
    exit(1);
}

$delivery = $argv[1];
$loginEmail = $argv[2];
$newPassword = $argv[3];
$host = $argv[4] ?? 'localhost';

echo "Sending reset link using delivery email: {$delivery} on host {$host}\n";

$server = [
    'HTTP_HOST' => $host,
    'SERVER_NAME' => $host,
    'REQUEST_URI' => '/forgot-password',
];

$req = Request::create('/forgot-password', 'POST', ['email' => $delivery], [], [], $server);

$ctrl = new PasswordResetController();
try {
    $resp = $ctrl->sendResetLink($req);
    echo "sendResetLink invoked.\n";
} catch (Throwable $e) {
    echo "sendResetLink error: " . $e->getMessage() . "\n";
}

// Wait briefly for DB writes
usleep(200000);

// Find token row for the account (it will store account email)
$row = DB::table('password_reset_tokens')->where('email', $loginEmail)->orderBy('created_at', 'desc')->first();
if (! $row) {
    // try delivery email
    $row = DB::table('password_reset_tokens')->where('email', $delivery)->orderBy('created_at', 'desc')->first();
}

if (! $row) {
    echo "No reset token found in central DB for {$loginEmail} or {$delivery}.\n";
    exit(1);
}

$token = $row->token;
echo "Found token for email {$row->email}: {$token}\n";

// Now submit reset form
$server2 = [ 'HTTP_HOST' => $host, 'SERVER_NAME' => $host, 'REQUEST_URI' => '/reset-password' ];
$resetReq = Request::create('/reset-password', 'POST', [
    'token' => $token,
    'email' => $row->email,
    'password' => $newPassword,
    'password_confirmation' => $newPassword,
], [], [], $server2);

try {
    $resp2 = $ctrl->reset($resetReq);
    echo "reset invoked.\n";
} catch (Throwable $e) {
    echo "reset error: " . $e->getMessage() . "\n";
}

// Try login
echo "Attempting login as {$loginEmail} with new password...\n";
$loginReq = Request::create('/login', 'POST', ['email' => $loginEmail, 'password' => $newPassword], [], [], ['HTTP_HOST' => $host]);
// attach session
try { $loginReq->setLaravelSession(app('session.store')); } catch (Throwable $e) {}

$auth = new SimpleAuthController();
try {
    $r = $auth->login($loginReq);
    if (method_exists($r, 'getStatusCode')) {
        echo "Login response status: " . $r->getStatusCode() . "\n";
    } else {
        echo "Login returned: " . gettype($r) . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "login error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
