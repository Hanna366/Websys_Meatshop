<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return redirect('/login');
        }

        $tenant = null;
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
        } else {
            // Fallback for cases where tenant middleware context is not initialized.
            $host = strtolower((string) $request->getHost());
            $tenant = Tenant::where('domain', $host)->first();
        }

        return view('dashboard.index', [
            'tenant' => $tenant,
        ]);
    }
}
