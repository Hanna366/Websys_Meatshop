<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        // $recaptchaSecret = (string) config('services.recaptcha.secret_key');
        // $host = strtolower((string) $request->getHost());
        // $isLocalHost = $host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.localhost');
        $recaptchaEnabled = false; // Temporarily disabled

        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'business_name' => 'required|string|max:255',
            'business_phone' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:1000',
            'plan' => 'required|in:basic,standard,premium,enterprise',
        ];

        // Validate the request data
        $request->validate($validationRules);

        $fullName = trim($request->name);
        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $firstName = $nameParts[0] ?? $fullName;
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        // Build a unique username from the submitted name.
        $baseUsername = strtolower(preg_replace('/[^a-z0-9]+/i', '', $fullName));
        if ($baseUsername === '') {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        $plainPassword = trim((string) $request->password);
        if ($plainPassword === '') {
            $plainPassword = Str::random(16);
        }

        // Create the tenant in the central system and prepare its database.
        $tenant = TenantService::createTenant([
            'business_name' => $request->business_name,
            'business_email' => $request->email,
            'business_phone' => $request->business_phone,
            'business_address' => $request->business_address,
            'plan' => $request->plan,
            'admin_name' => $fullName,
            'admin_email' => $request->email,
            'password' => $plainPassword,
            'subscription' => [
                'plan' => $request->plan,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ],
        ]);

        // Create the user in the central system so the tenant can log in.
        // The tenant's own database also has a users table, but we use the central
        // users table for authentication across the platform.
        User::create([
            'tenant_id' => $tenant->tenant_id,
            'username' => $username,
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => 'owner',
            'profile' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName,
            ],
        ]);

        // Redirect to a success page or login.
        // Onboarding email is sent by TenantService after provisioning.
        $message = 'Account created successfully! Check your email for login instructions.';
        
        return redirect('/login')->with('success', $message);
    }
}