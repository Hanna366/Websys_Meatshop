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

        // Demo login - accept admin@meatshop.com / password
        if ($credentials['email'] === 'admin@meatshop.com' && $credentials['password'] === 'password') {
            // Set a simple session
            session(['authenticated' => true, 'user' => ['email' => 'admin@meatshop.com']]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}
