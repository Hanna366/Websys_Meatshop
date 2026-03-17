<?php

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('domains') || !Schema::hasTable('tenants') || !Schema::hasColumn('tenants', 'domain')) {
            return;
        }

        Tenant::query()
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->get(['id', 'domain'])
            ->each(function (Tenant $tenant) {
                Domain::firstOrCreate([
                    'domain' => $tenant->domain,
                ], [
                    'tenant_id' => $tenant->id,
                ]);
            });
    }

    public function down(): void
    {
        // No destructive rollback for backfill migration.
    }
};
