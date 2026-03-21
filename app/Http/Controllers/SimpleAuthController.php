<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        // Attempt to authenticate against the user table first.
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $tenant = $user->tenant;
            $plan = 'Basic';
            $features = ['Up to 100 products', 'Inventory tracking', 'Single user'];

            if ($tenant) {
                $plan = ucfirst($tenant->plan ?? 'basic');
                $features = [];
            }

            session([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'tenant_id' => $user->tenant_id,
                    'plan' => $plan,
                    'features' => $features,
                ]
            ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }


    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }
}
