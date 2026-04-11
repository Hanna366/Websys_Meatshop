<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\User;

$business = $argv[1] ?? 'Caberto';
$adminEmail = $argv[2] ?? 'areshanna088+cab@gmail.com';

$t = Tenant::where('business_name', $business)->first();
if (!$t) {
    echo "No tenant found for business '{$business}'\n";
    exit(0);
}

echo "TENANT:\n";
print_r($t->toArray());

try {
    $dbPlain = $t->db_password ? decrypt($t->db_password) : null;
    echo "DECRYPTED_DB_PASSWORD: " . ($dbPlain ?: '(empty)') . "\n";
} catch (Throwable $e) {
    echo "DECRYPTED_DB_PASSWORD: <decrypt failed>\n";
}

echo "ONBOARDING_EMAIL_SENT: " . (!empty($t->onboarding_email_sent) ? 'true' : 'false') . "\n";

echo "--- ADMIN USER (tenant connection) ---\n";
$u = User::on('tenant')->where('email', $adminEmail)->first();
if (!$u) {
    echo "No admin user found in tenant DB for {$adminEmail}\n";
    exit(0);
}

print_r($u->toArray());

// Show whether the password verifies against the generated password stored anywhere
if (!empty($t->onboarding_email_sent) && property_exists($t, 'generated_temp_password')) {
    echo "GENERATED_PASSWORD_IN_TENANT_OBJECT: " . ($t->generated_temp_password ?? '(not present)') . "\n";
}

// End.

