<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Domain;
use App\Models\Tenant;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PasswordResetController extends Controller
{
    /**
     * Show password reset request form
     */
    public function showRequestForm()
    {
        return view('auth.password-reset-request');
    }

    /**
     * Send password reset email
     */
    public function sendResetLink(Request $request)
    {
        // Basic format validation first. Existence will be checked after
        // resolving tenant context so we validate against the correct DB.
        $request->validate([
            'email' => 'required|email',
        ]);

        // Resolve tenant context from host (if present) so tokens and user updates touch the tenant DB
        $host = strtolower((string) $request->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));
        $tenant = null;

        if ($host !== '' && !in_array($host, $centralDomains, true)) {
            if (Schema::hasTable('domains')) {
                $domain = Domain::where('domain', $host)->first();
                if ($domain && $domain->tenant) {
                    $tenant = $domain->tenant;
                }
            }

            if (!$tenant && Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
                $tenant = Tenant::where('domain', $host)->first();
            }

            if ($tenant) {
                app()->instance('tenant', $tenant);
                config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
                DB::purge('tenant');
            }
        }

        // Treat the submitted `email` as the recovery/delivery address.
        $deliveryEmail = (string) $request->email;

        // Try to locate the actual user account (login email) that this
        // recovery address should deliver a reset for. Accept the following:
        //  - submitted address equals user.email (login)
        //  - submitted address equals user.recovery_email
        //  - normalized plus-address variants that map to a tenant user
        $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $accountUser = null;
        $userConnection = null; // 'tenant' or central connection name

        // 1) Exact match on tenant when tenant context bound
        if ($tenant) {
            $accountUser = User::on('tenant')->where(function ($q) use ($deliveryEmail) {
                $q->where('email', $deliveryEmail)->orWhere('recovery_email', $deliveryEmail);
            })->first();
            if ($accountUser) {
                $userConnection = 'tenant';
            }
        }

        // 2) Exact match on central
        if (! $accountUser) {
            $centralMatch = User::on($centralConn)->where(function ($q) use ($deliveryEmail) {
                $q->where('email', $deliveryEmail)->orWhere('recovery_email', $deliveryEmail);
            })->first();
            if ($centralMatch) {
                $accountUser = $centralMatch;
                $userConnection = $centralConn;
            }
        }

        // 3) Normalized plus-tag match in tenant
        if (! $accountUser && $tenant && strpos($deliveryEmail, '@') !== false) {
            [$local, $domain] = explode('@', $deliveryEmail, 2);
            $base = preg_replace('/\+.*$/', '', $local);
            $alt = User::on('tenant')->where('email', 'like', $base . '%@' . $domain)->first();
            if ($alt) {
                $accountUser = $alt;
                $userConnection = 'tenant';
            }
        }

        // 4) Normalized plus-tag match in central (ensure it belongs to tenant if tenant bound)
        if (! $accountUser && strpos($deliveryEmail, '@') !== false) {
            [$local, $domain] = explode('@', $deliveryEmail, 2);
            $base = preg_replace('/\+.*$/', '', $local);
            $centralAlt = User::on($centralConn)->where('email', 'like', $base . '%@' . $domain)->first();
            if ($centralAlt) {
                if (! $tenant || (! empty($centralAlt->tenant_id) && $centralAlt->tenant_id === $tenant->tenant_id)) {
                    $accountUser = $centralAlt;
                    $userConnection = $centralConn;
                }
            }
        }

        if (! $accountUser) {
            return back()->withErrors(['email' => 'We cannot find a user with that email address for this tenant.'])->withInput();
        }

        // Generate token for the actual account user's login email
        $resetToken = Str::random(60);

        $tokensConnection = $userConnection === 'tenant' ? DB::connection('tenant') : DB::connection();
        $tokensConnection->table('password_reset_tokens')->updateOrInsert(
            ['email' => $accountUser->email],
            [
                'token' => $resetToken,
                'created_at' => now(),
                'expires_at' => now()->addHour(),
            ]
        );

        // Prepare branding and destination host: prefer tenant domain when available
        $businessName = 'Meat Shop POS';
        $preferredHost = $request->getHost();
        $tenantForEmail = null;
        if (! empty($accountUser->tenant_id)) {
            $tenantForEmail = \App\Models\Tenant::where('tenant_id', $accountUser->tenant_id)->first();
        }

        if ($tenantForEmail) {
            $businessName = $tenantForEmail->business_name ?? $businessName;
            $preferredHost = $tenantForEmail->domain ?? $preferredHost;
        } elseif (app()->bound('tenant') && tenant()) {
            $preferredHost = tenant()->domain ?? $preferredHost;
            $businessName = tenant()->business_name ?? $businessName;
        }

        $scheme = $request->getScheme() ?: 'https';
        $port = (int) $request->getPort();
        if ($port && ! in_array($port, [80, 443], true)) {
            $hostWithPort = $preferredHost . ':' . $port;
        } else {
            $hostWithPort = $preferredHost;
        }
        $resetUrlHost = $scheme . '://' . $hostWithPort;

        // Sender details
        $fromAddress = null;
        $fromName = null;
        if ($tenantForEmail && ! empty($tenantForEmail->business_email)) {
            $fromAddress = $tenantForEmail->business_email;
            $fromName = $tenantForEmail->business_name ?? $businessName;
        }

        $userName = $accountUser->name ?? null;

        // Alternate emails for suggestion (other emails in tenant for same base)
        $altEmails = [];
        try {
            if ($tenantForEmail && strpos($deliveryEmail, '@') !== false) {
                [$local, $domain] = explode('@', $deliveryEmail, 2);
                $base = preg_replace('/\+.*$/', '', $local);
                $altEmails = User::on('tenant')
                    ->where('tenant_id', $tenantForEmail->tenant_id)
                    ->where('email', 'like', $base . '%@' . $domain)
                    ->pluck('email')
                    ->toArray();
            }
        } catch (\Throwable $e) {
            $altEmails = [];
        }

        // Send email to the recovery/delivery address but include account details
        $emailResult = EmailService::sendPasswordReset(
            $deliveryEmail,
            $businessName,
            $resetToken,
            $resetUrlHost,
            $fromAddress,
            $fromName,
            $userName,
            $altEmails
        );

        // Log email result
        if (!$emailResult['success']) {
            \Log::error('Failed to send password reset email: ' . $emailResult['error']);
        }

        $message = $emailResult['success']
            ? 'Password reset link has been sent to your email.'
            : 'Failed to send password reset email. Please try again later.';

        return back()->with('status', $message);
    }

    /**
     * Show password reset form with token
     */
    public function showResetForm($token)
    {
        // Determine tenant context from current host so we query the right tokens table
        $host = strtolower((string) request()->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));
        $tenant = null;

        if ($host !== '' && !in_array($host, $centralDomains, true)) {
            if (Schema::hasTable('domains')) {
                $domain = Domain::where('domain', $host)->first();
                if ($domain && $domain->tenant) {
                    $tenant = $domain->tenant;
                }
            }

            if (!$tenant && Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
                $tenant = Tenant::where('domain', $host)->first();
            }

            if ($tenant) {
                app()->instance('tenant', $tenant);
                config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
                DB::purge('tenant');
            }
        }

        $tokensTable = $tenant ? DB::connection('tenant') : DB::connection();

        $resetToken = $tokensTable->table('password_reset_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        $tokenOnTenant = $tenant && $resetToken;

        // Fallback: if running in tenant context but token wasn't found there,
        // check central connection (some flows may create tokens on central DB).
        if (!$resetToken && $tenant) {
            $centralToken = DB::connection()->table('password_reset_tokens')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($centralToken) {
                $resetToken = $centralToken;
                $tokenOnTenant = false;
                $tokensTable = DB::connection();
            }
        }

        if (!$resetToken) {
            return view('auth.password-reset')->with([
                'error' => 'Invalid or expired reset token.',
                'token' => null
            ]);
        }

        return view('auth.password-reset')->with([
            'token' => $token,
            'email' => $resetToken->email
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Resolve tenant context from host
        $host = strtolower((string) $request->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));
        $tenant = null;

        if ($host !== '' && !in_array($host, $centralDomains, true)) {
            if (Schema::hasTable('domains')) {
                $domain = Domain::where('domain', $host)->first();
                if ($domain && $domain->tenant) {
                    $tenant = $domain->tenant;
                }
            }

            if (!$tenant && Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
                $tenant = Tenant::where('domain', $host)->first();
            }

            if ($tenant) {
                app()->instance('tenant', $tenant);
                config(['database.connections.tenant' => $tenant->getTenantDatabaseConfig()]);
                DB::purge('tenant');
            }
        }

        $tokensTable = $tenant ? DB::connection('tenant') : DB::connection();

        // Look up the reset token by token value only (allow clicking from different email address).
        $resetToken = $tokensTable->table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('expires_at', '>', now())
            ->first();

        $tokenOnTenant = $tenant && $resetToken;

        // Fallback to central connection if token not found in tenant
        if (!$resetToken && $tenant) {
            $centralToken = DB::connection()->table('password_reset_tokens')
                ->where('token', $request->token)
                ->where('expires_at', '>', now())
                ->first();

            if ($centralToken) {
                $resetToken = $centralToken;
                $tokenOnTenant = false;
                $tokensTable = DB::connection();
            }
        }

        if (!$resetToken) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        $submittedEmail = (string) $request->email;
        $tokenEmail = (string) ($resetToken->email ?? $submittedEmail);

        // Prefer finding the user by the token email (the account's login
        // address). Try tenant connection first when bound, then central,
        // with normalized plus-address fallbacks and central->tenant copy
        // when tenant_id matches.
        $user = null;

        if ($tenant) {
            try {
                $user = User::on('tenant')->where('email', $tokenEmail)->first();
            } catch (\Throwable $e) {
                \Log::warning('Tenant lookup failed during password reset', ['error' => $e->getMessage()]);
            }

            // normalized plus-address fallback on tenant
            if (! $user && strpos($tokenEmail, '@') !== false) {
                try {
                    [$local, $domain] = explode('@', $tokenEmail, 2);
                    $normalized = preg_replace('/\+.*$/', '', $local);
                    $user = User::on('tenant')
                        ->where('email', 'like', $normalized . '+%@' . $domain)
                        ->orWhere('email', $tokenEmail)
                        ->first();
                } catch (\Throwable $e) {
                    \Log::warning('Alt tenant email lookup failed during reset', ['email' => $tokenEmail, 'error' => $e->getMessage()]);
                }
            }
        }

        // central/default connection lookup
        if (! $user) {
            try {
                $user = User::where('email', $tokenEmail)->first();
            } catch (\Throwable $e) {
                \Log::warning('Central lookup failed during password reset', ['error' => $e->getMessage()]);
            }
        }

        // If still not found and tenant bound, try copying central user into tenant
        // when tenant_id matches, then reload from tenant.
        if (! $user && $tenant) {
            try {
                $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
                $centralUser = User::on($centralConn)->where('email', $tokenEmail)->first();
                if ($centralUser && ! empty($centralUser->tenant_id) && $centralUser->tenant_id === $tenant->tenant_id) {
                    $now = now();
                    $tenantValues = [
                        'tenant_id' => $tenant->tenant_id,
                        'username' => $centralUser->username ?? (string) Str::slug($centralUser->email, '_'),
                        'name' => $centralUser->name ?? '',
                        'email' => $centralUser->email,
                        'password' => $centralUser->password,
                        'role' => $centralUser->role ?? 'user',
                        'profile' => is_null($centralUser->profile) ? json_encode(new \stdClass()) : (is_string($centralUser->profile) ? $centralUser->profile : json_encode($centralUser->profile)),
                        'permissions' => is_null($centralUser->permissions) ? json_encode(new \stdClass()) : (is_string($centralUser->permissions) ? $centralUser->permissions : json_encode($centralUser->permissions)),
                        'preferences' => is_null($centralUser->preferences) ? null : (is_string($centralUser->preferences) ? $centralUser->preferences : json_encode($centralUser->preferences)),
                        'last_login' => null,
                        'login_attempts' => 0,
                        'lock_until' => null,
                        'status' => $centralUser->status ?? 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    DB::connection('tenant')->table('users')->updateOrInsert(
                        ['email' => $centralUser->email],
                        $tenantValues
                    );

                    $user = User::on('tenant')->where('email', $tokenEmail)->first();
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to copy central user to tenant DB during password reset', ['email' => $tokenEmail, 'error' => $e->getMessage()]);
            }
        }

        // Final fallback: try submitted (delivery) email on tenant then central
        if (! $user) {
            try {
                if ($tenant) {
                    $user = User::on('tenant')->where('email', $submittedEmail)->first();
                }
            } catch (\Throwable $e) {
                // ignore
            }

            if (! $user) {
                try {
                    $user = User::where('email', $submittedEmail)->first();
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->login_attempts = 0;
            $user->lock_until = null;
            $user->save();

            // Delete the reset token
            $tokensTable->table('password_reset_tokens')
                ->where('token', $request->token)
                ->delete();

            return redirect('/login')->with('success', 'Password has been reset successfully!');
        }

        return back()->withErrors(['email' => 'Unable to reset password. Please try again.']);
    }
}
