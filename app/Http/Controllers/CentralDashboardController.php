<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\UpdateRequest;
use App\Models\Version;
use App\Services\DatabaseMonitorService;
use App\Services\VersionManagementService;

class CentralDashboardController extends Controller
{
    public function welcome()
    {
        return view('central.welcome');
    }

    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'unpaid_tenants' => Tenant::where('status', 'unpaid')->count(),
        ];

        $tenants = Tenant::query()
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        // Get database monitoring statistics
        $databaseStats = DatabaseMonitorService::getDatabaseUsageStats();
        $databaseSummary = DatabaseMonitorService::getDatabaseSummary();
        $databaseHealth = DatabaseMonitorService::getDatabaseHealth();
        
        // Check XAMPP MySQL status
        $xamppStatus = DatabaseMonitorService::checkXAMPPStatus();

        // Pending update requests (from tenants)
        $pendingUpdateRequests = UpdateRequest::where('status', 'pending')->count();

        $currentVersion = VersionManagementService::getCurrentVersion();
        $latest = Version::where('status', 'stable')->orderBy('release_date', 'desc')->first();
        $latestVersion = $latest->version ?? $currentVersion;

        return view('central.home', [
            'stats' => $stats,
            'tenants' => $tenants,
            'database_stats' => $databaseStats,
            'database_summary' => $databaseSummary,
            'database_health' => $databaseHealth,
            'xampp_status' => $xamppStatus,
            'pending_update_requests' => $pendingUpdateRequests,
            'currentVersion' => $currentVersion,
            'latestVersion' => $latestVersion,
        ]);
    }
}
