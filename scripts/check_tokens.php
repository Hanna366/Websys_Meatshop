<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$emails = array_slice($argv, 1);
if (empty($emails)) {
    echo "Usage: php scripts/check_tokens.php email1 [email2 ...]\n";
    exit(1);
}

foreach ($emails as $email) {
    echo "\n--- Checking tokens for: {$email} ---\n";
    try {
        $rows = DB::table('password_reset_tokens')->where('email', $email)->orderBy('created_at', 'desc')->get();
        if ($rows->isEmpty()) {
            echo "Central: no token rows found.\n";
        } else {
            foreach ($rows as $r) {
                $te = property_exists($r, 'token_encrypted') ? ($r->token_encrypted ? 'yes' : 'no') : 'n/a';
                echo "Central: token hash len=" . strlen($r->token) . " encrypted={$te} created_at={$r->created_at}\n";
            }
        }
    } catch (Throwable $e) {
        echo "Central lookup error: " . $e->getMessage() . "\n";
    }

    // tenant connection may not be configured, but attempt
    try {
        $rowsT = DB::connection('tenant')->table('password_reset_tokens')->where('email', $email)->orderBy('created_at', 'desc')->get();
        if ($rowsT->isEmpty()) {
            echo "Tenant: no token rows found or tenant not configured.\n";
        } else {
            foreach ($rowsT as $r) {
                $te = property_exists($r, 'token_encrypted') ? ($r->token_encrypted ? 'yes' : 'no') : 'n/a';
                echo "Tenant: token hash len=" . strlen($r->token) . " encrypted={$te} created_at={$r->created_at}\n";
            }
        }
    } catch (Throwable $e) {
        echo "Tenant lookup error: " . $e->getMessage() . "\n";
    }
}

echo "\nFinished.\n";
