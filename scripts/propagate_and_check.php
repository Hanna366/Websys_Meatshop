<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

try {
    echo "Running syncReleases()...\n";
    $res = \App\Services\GitHubService::syncReleases();
    echo "SYNC RESULT:\n";
    echo json_encode($res, JSON_PRETTY_PRINT) . "\n\n";

    $latest = \App\Models\Version::where('is_stable', true)
        ->when(Schema::hasColumn('versions', 'is_available_to_tenants'), function ($q) { return $q->where('is_available_to_tenants', true); })
        ->orderBy('version', 'desc')
        ->first();

    echo "CENTRAL LATEST VERSION:\n";
    echo json_encode($latest?->toArray(), JSON_PRETTY_PRINT) . "\n\n";

    $tenant = \Stancl\Tenancy\Database\Models\Tenant::first();
    if (!$tenant) {
        echo "No tenants found.\n";
        exit(0);
    }

    echo "Initializing tenant: " . ($tenant->id ?? $tenant->tenant_id ?? '') . "\n";
    tenancy()->initialize($tenant);

    $tu = \App\Models\TenantUpdate::first();
    echo "TENANT tenant_updates record:\n";
    echo json_encode($tu?->toArray(), JSON_PRETTY_PRINT) . "\n\n";

    tenancy()->end();

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}


