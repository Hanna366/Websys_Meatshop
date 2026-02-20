<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpleAuthController extends Controller
{
    public function showLoginForm()
    {
        // If already authenticated, redirect to dashboard
        if (session('authenticated')) {
            return redirect('/dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Demo accounts with different subscription plans
        $demoAccounts = [
            'basic@meatshop.com' => [
                'password' => 'basic123',
                'plan' => 'Basic',
                'features' => ['Up to 100 products', 'Inventory tracking', 'Single user']
            ],
            'standard@meatshop.com' => [
                'password' => 'standard123', 
                'plan' => 'Standard',
                'features' => ['Unlimited products', 'Full POS system', 'Up to 3 users', 'Customer management']
            ],
            'premium@meatshop.com' => [
                'password' => 'premium123',
                'plan' => 'Premium', 
                'features' => ['All features', 'Advanced analytics', 'API access', 'Unlimited users', 'Priority support']
            ]
        ];

        if (isset($demoAccounts[$credentials['email']]) && $demoAccounts[$credentials['email']]['password'] === $credentials['password']) {
            $account = $demoAccounts[$credentials['email']];
            session([
                'authenticated' => true,
                'user' => [
                    'email' => $credentials['email'],
                    'plan' => $account['plan'],
                    'features' => $account['features']
                ]
            ]);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials. Try basic@meatshop.com / basic123, standard@meatshop.com / standard123, or premium@meatshop.com / premium123',
        ]);
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}
