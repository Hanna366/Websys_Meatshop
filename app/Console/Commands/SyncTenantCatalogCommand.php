<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncTenantCatalogCommand extends Command
{
    protected $signature = 'tenant:sync-catalog
        {tenant_id? : Optional tenant_id UUID (sync all tenants when omitted)}
        {--skip-migrate : Skip tenant migrations and run only seeders}';

    protected $description = 'Run tenant catalog migrations and seed Kitayama Retail 2025 data into tenant databases.';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        $query = Tenant::query();
        if (!empty($tenantId)) {
            $query->where('tenant_id', (string) $tenantId);
        }

        $tenants = $query->get();
        if ($tenants->isEmpty()) {
            $this->error('No tenants found to sync.');
            return self::FAILURE;
        }

        $synced = 0;
        foreach ($tenants as $tenant) {
            $this->line('Syncing tenant: ' . $tenant->tenant_id . ' (' . ($tenant->business_name ?? 'N/A') . ')');

            try {
                if (!$this->option('skip-migrate')) {
                    TenantService::runTenantMigrations($tenant);
                }

                TenantService::runTenantSeeders($tenant);

                $diagnostics = $tenant->run(function () {
                    return [
                        'connection' => (string) config('database.default'),
                        'database' => (string) config('database.connections.tenant.database'),
                        'products_table' => Schema::connection('tenant')->hasTable('products'),
                        'products_count' => Schema::connection('tenant')->hasTable('products')
                            ? (int) DB::connection('tenant')->table('products')->count()
                            : 0,
                    ];
                });

                $synced++;
                $this->info('  -> Catalog synced successfully.');
                $this->line('  -> Connection: ' . $diagnostics['connection']);
                $this->line('  -> Database: ' . $diagnostics['database']);
                $this->line('  -> Products table: ' . ($diagnostics['products_table'] ? 'yes' : 'no'));
                $this->line('  -> Products count: ' . (int) $diagnostics['products_count']);
            } catch (\Throwable $e) {
                $this->error('  -> Failed: ' . $e->getMessage());
            }
        }

        $this->info("Completed. Synced {$synced} tenant(s).");

        return self::SUCCESS;
    }
}
