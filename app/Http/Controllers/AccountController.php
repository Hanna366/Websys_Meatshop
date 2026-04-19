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

        // Create the tenant central record but do NOT provision it yet.
        // Provisioning (DB, tenant migrations, tenant admin user, onboarding
        // email) will occur only after central admin approval.
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
                // Do not mark active yet; the tenant requires approval.
                'status' => 'pending',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ],
        ], false);

        // Do NOT create a central user for the tenant admin. Tenant admin
        // accounts must live only in the tenant database after provisioning.

        // Redirect to a success page or login.
        // Onboarding email is sent by TenantService after provisioning.
        $message = 'Tenant request submitted. It will be provisioned after central approval.';

        return redirect('/')->with('success', $message);
    }
}