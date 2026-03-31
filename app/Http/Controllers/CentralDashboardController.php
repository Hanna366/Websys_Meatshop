<?php

namespace App\Http\Controllers;

use App\Models\Tenant;

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

        return view('central.home', [
            'stats' => $stats,
            'tenants' => $tenants,
        ]);
    }
}
