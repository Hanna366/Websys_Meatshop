<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Domain;
use App\Services\TenantService;

if ($argc < 4) {
    echo "Usage: php scripts/create_and_provision_tenant.php BUSINESS_NAME ADMIN_EMAIL PLAN [DOMAIN]\n";
    exit(1);
}

$business = $argv[1];
$adminEmail = $argv[2];
$plan = $argv[3];
$domain = $argv[4] ?? null;

// Create central tenant record (pending)
$tenant = Tenant::create([
    'tenant_id' => (string) \Illuminate\Support\Str::uuid(),
    'business_name' => $business,
    'business_email' => $adminEmail,
    'business_phone' => '',
    'admin_name' => $business,
    'admin_email' => $adminEmail,
    'business_address' => [],
    'plan' => $plan,
    'domain' => $domain,
    'status' => 'pending',
    'subscription' => [
        'plan' => $plan,
        'status' => 'pending',
    ],
    'settings' => [],
    'usage' => [],
    'limits' => [],
]);

echo "Created central tenant (id={$tenant->tenant_id})\n";

if (!empty($domain)) {
    // ensure domains table maps the domain to tenant for tenancy resolver
    if (Schema::hasTable('domains')) {
        Domain::firstOrCreate([
            'domain' => $domain,
        ], [
            'tenant_id' => $tenant->id,
        ]);
    }
}

try {
    $result = TenantService::provisionTenant($tenant, true);
    if (is_array($result)) {
        $provisioned = $result['tenant'];
        echo "Provisioning complete. Onboarding email sent: " . ($provisioned->onboarding_email_sent ? 'true' : 'false') . "\n";
        echo "Generated temporary admin password: " . ($result['generated_password'] ?? '(none)') . "\n";
    } else {
        $provisioned = $result;
        echo "Provisioning complete. Onboarding email sent: " . ($provisioned->onboarding_email_sent ? 'true' : 'false') . "\n";
        echo "No generated password available (maybe a provided password was used).\n";
    }
} catch (\Throwable $e) {
    echo "Provisioning failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
