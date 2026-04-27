<?php

namespace App\Jobs;

use App\Models\TenantUpdateRequest;
use App\Models\UpdateLog;
use App\Services\VersionManagementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplyTenantUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantUpdateRequest;

    public function __construct(TenantUpdateRequest $tenantUpdateRequest)
    {
        $this->tenantUpdateRequest = $tenantUpdateRequest;
    }

    public function handle()
    {
        try {
            $request = $this->tenantUpdateRequest;
            $tenantUuid = $request->tenant_id;
            $fromVersion = $request->current_version;
            $toVersion = $request->requested_version;

            // Convert UUID to integer ID for UpdateLog
            $tenant = \App\Models\Tenant::where('tenant_id', $tenantUuid)->first();
            if (!$tenant) {
                Log::error("Tenant not found for update", ['tenant_uuid' => $tenantUuid]);
                return;
            }
            $tenantId = $tenant->id;

            Log::info("Applying tenant update", [
                'tenant_uuid' => $tenantUuid,
                'tenant_id' => $tenantId,
                'from' => $fromVersion,
                'to' => $toVersion
            ]);

            // Create update log entry
            UpdateLog::create([
                'tenant_id' => $tenantId,
                'from_version' => $fromVersion,
                'to_version' => $toVersion,
                'status' => 'completed',
                'update_data' => [
                    'applied_by' => 'system',
                    'applied_at' => now()->toDateTimeString(),
                    'request_id' => $request->id
                ],
                'started_at' => now(),
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update request status to completed
            $request->status = 'completed';
            $request->processed_at = now();
            $request->save();

            Log::info("Tenant update completed successfully", [
                'tenant_id' => $tenantId,
                'version' => $toVersion
            ]);

        } catch (\Throwable $e) {
            Log::error("Failed to apply tenant update", [
                'tenant_id' => $this->tenantUpdateRequest->tenant_id,
                'error' => $e->getMessage()
            ]);

            // Mark as failed
            $this->tenantUpdateRequest->status = 'failed';
            $this->tenantUpdateRequest->save();
        }
    }
}
