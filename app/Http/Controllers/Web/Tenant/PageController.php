<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function products()
    {
        return view('products');
    }

    public function inventory()
    {
        return view('inventory');
    }

    public function sales()
    {
        return view('sales');
    }

    public function customers()
    {
        return view('customers');
    }

    public function suppliers()
    {
        return view('suppliers');
    }

    public function reports()
    {
        return view('reports');
    }

    public function settings()
    {
        return view('settings');
    }

    public function profile()
    {
        return view('profile');
    }
}
