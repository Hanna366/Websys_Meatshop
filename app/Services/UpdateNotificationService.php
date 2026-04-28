<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUpdate;
use Illuminate\Support\Facades\Schema;
use App\Notifications\UpdateAvailable;
use App\Notifications\UpdateCompleted;
use App\Notifications\UpdateFailed;
use Illuminate\Support\Facades\Log;

class UpdateNotificationService
{
    /**
     * Notify all tenants about available update
     */
    public static function notifyUpdateAvailable(array $updateInfo): void
    {
        try {
            // Get all admin users across all tenants
            $tenants = Tenant::where('status', 'active')->get();
            
            foreach ($tenants as $tenant) {
                // Initialize tenant context
                tenancy()->initialize($tenant);
                
                // Get admin users for this tenant
                $adminUsers = User::where('role', 'Administrator')
                    ->orWhere('role', 'Owner')
                    ->get();
                
                foreach ($adminUsers as $user) {
                    $user->notify(new UpdateAvailable($updateInfo));
                }

                // Also persist a tenant-scoped pointer so the tenant UI shows the available version
                try {
                    if (Schema::hasTable('tenant_updates')) {
                        $latestVersion = $updateInfo['latest_version'] ?? ($updateInfo['update_info']['version'] ?? null);
                        $notes = $updateInfo['update_info']['description'] ?? null;

                        $existing = TenantUpdate::first();
                        if ($existing) {
                            $existing->update([
                                'available_version' => $latestVersion,
                                'release_notes' => $notes,
                                'last_checked_at' => now(),
                            ]);
                        } else {
                            TenantUpdate::create([
                                'current_version' => null,
                                'available_version' => $latestVersion,
                                'release_notes' => $notes,
                                'last_checked_at' => now(),
                                'force_update' => false,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed updating tenant_updates table for tenant ' . ($tenant->id ?? 'unknown') . ': ' . $e->getMessage());
                }

                // End tenant context
                tenancy()->end();
            }
            
            Log::info("Update notifications sent for version {$updateInfo['latest_version']}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send update notifications: " . $e->getMessage());
        }
    }

    /**
     * Notify specific tenant about update completion
     */
    public static function notifyUpdateCompleted(int $tenantId, array $updateInfo): void
    {
        try {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) return;
            
            tenancy()->initialize($tenant);
            
            $adminUsers = User::where('role', 'Administrator')
                ->orWhere('role', 'Owner')
                ->get();
            
            foreach ($adminUsers as $user) {
                $user->notify(new UpdateCompleted($updateInfo));
            }
            
            tenancy()->end();
            
            Log::info("Update completion notification sent for tenant {$tenantId}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send update completion notification: " . $e->getMessage());
        }
    }

    /**
     * Notify about update failure
     */
    public static function notifyUpdateFailed(int $tenantId, array $updateInfo): void
    {
        try {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) return;
            
            tenancy()->initialize($tenant);
            
            $adminUsers = User::where('role', 'Administrator')
                ->orWhere('role', 'Owner')
                ->get();
            
            foreach ($adminUsers as $user) {
                $user->notify(new UpdateFailed($updateInfo));
            }
            
            tenancy()->end();
            
            Log::error("Update failure notification sent for tenant {$tenantId}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send update failure notification: " . $e->getMessage());
        }
    }

    /**
     * Check and send update notifications if needed
     */
    public static function checkAndNotify(): void
    {
        $updateInfo = VersionManagementService::checkForUpdates();
        
        if ($updateInfo['update_available']) {
            self::notifyUpdateAvailable($updateInfo);
        }
    }
}
