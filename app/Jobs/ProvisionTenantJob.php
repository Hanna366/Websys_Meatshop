<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly string $tenantId, private readonly bool $returnPassword = false)
    {
    }

    public function handle(): void
    {
        try {
            $tenant = Tenant::where('tenant_id', $this->tenantId)->firstOrFail();
            TenantService::provisionTenant($tenant, $this->returnPassword);
        } catch (Throwable $e) {
            \Log::error('ProvisionTenantJob failed.', ['tenant_id' => $this->tenantId, 'error' => $e->getMessage()]);
        }
    }
}
