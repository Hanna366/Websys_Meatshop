<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Check if user is authenticated
        if (!session('authenticated')) {
            return redirect('/login');
        }

        $tenant = null;
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
        }

        return view('dashboard.index', [
            'tenant' => $tenant,
        ]);
    }
}
