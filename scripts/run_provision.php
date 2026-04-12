<?php

use App\Models\Tenant;
use App\Services\TenantService;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$term = isset($argv[1]) ? trim($argv[1]) : '';

if ($term === '') {
    echo "Usage: php scripts/run_provision.php {tenant_id|business_name}\n";
    exit(1);
}

// Try find by tenant_id first, then by business_name (case-insensitive)
$tenant = Tenant::where('tenant_id', $term)
    ->orWhereRaw('LOWER(business_name) = ?', [mb_strtolower($term)])
    ->first();
if (!$tenant) {
    echo "Tenant not found: {$tenantId}\n";
    exit(1);
}

try {
    echo "Provisioning tenant: " . ($tenant->business_name ?? $tenant->tenant_id) . "\n";
    $result = TenantService::provisionTenant($tenant);
    echo "Provisioning finished. Onboarding email sent: " . ($result->onboarding_email_sent ? 'true' : 'false') . "\n";
} catch (\Throwable $e) {
    echo "Provisioning failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
