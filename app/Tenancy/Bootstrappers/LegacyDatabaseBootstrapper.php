<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class LegacyDatabaseBootstrapper implements TenancyBootstrapper
{
    private ?string $previousDefaultConnection = null;

    public function bootstrap(Tenant $tenant)
    {
        $this->previousDefaultConnection = config('database.default');

        if (! method_exists($tenant, 'getTenantDatabaseConfig')) {
            return;
        }

        config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
        DB::purge('tenant');
        config(['database.default' => 'tenant']);

        app()->instance('tenant', $tenant);
    }

    public function revert()
    {
        DB::purge('tenant');

        if ($this->previousDefaultConnection) {
            config(['database.default' => $this->previousDefaultConnection]);
        }

        app()->forgetInstance('tenant');
    }
}
