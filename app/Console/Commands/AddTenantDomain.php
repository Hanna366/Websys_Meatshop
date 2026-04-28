<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;
use Exception;

class AddTenantDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:add-domain {tenant : Tenant id or uuid} {domain : Domain to add} {--force : Force execution outside local env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a domain row for a tenant (safe helper for local development)';

    public function handle(): int
    {
        if (!app()->environment('local') && !$this->option('force')) {
            $this->error('This command is intended for local development. Re-run with --force to proceed.');
            return 1;
        }

        $identifier = $this->argument('tenant');
        $domain = $this->argument('domain');

        try {
            // Prefer primary key lookup first (id), then fall back to uuid if the column exists.
            $tenant = null;
            if (is_numeric($identifier)) {
                $tenant = Tenant::find($identifier);
            }

            if (! $tenant && Schema::hasColumn('tenants', 'uuid')) {
                $tenant = Tenant::where('uuid', $identifier)->first();
            }

            // Final fallback: try finding by id regardless (handles string numeric ids)
            if (! $tenant) {
                $tenant = Tenant::find($identifier);
            }

            if (! $tenant) {
                $this->error("Tenant not found for identifier: {$identifier}");
                return 1;
            }

            if (! method_exists($tenant, 'domains')) {
                $this->error('Tenant model does not define a domains() relation.');
                return 1;
            }

            $exists = $tenant->domains()->where('domain', $domain)->exists();
            if ($exists) {
                $this->info("Domain '{$domain}' already exists for tenant {$tenant->id}.");
                return 0;
            }

            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            $this->info("Added domain '{$domain}' to tenant {$tenant->id} ({$tenant->business_name}).");
            return 0;
        } catch (Exception $e) {
            $this->error('Failed to add domain: ' . $e->getMessage());
            return 1;
        }
    }
}
