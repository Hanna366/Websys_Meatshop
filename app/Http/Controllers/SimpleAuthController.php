<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SimpleAuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $currentContext = $this->currentAuthContext();
        // $showRecaptcha = $currentContext === 'central' && (bool) config('services.recaptcha.site_key');
        $showRecaptcha = false; // Temporarily disabled

        // Get tenant information for dynamic logo
        $tenant = null;
        if (str_starts_with($currentContext, 'tenant:')) {
            $tenant = (app()->bound('tenant') && tenant()) ? tenant() : $this->resolveTenantFromHost($request);
        }

        if ($request->boolean('force_login')) {
            session()->forget(['authenticated', 'auth_context', 'user']);
            return view('auth.login', [
                'showRecaptcha' => $showRecaptcha,
                'tenant' => $tenant
            ]);
        }

        // Tenant domains should always show the login screen when opened.
        if (str_starts_with($currentContext, 'tenant:')) {
            if (session('authenticated') && session('auth_context') === $currentContext) {
                session()->forget(['authenticated', 'auth_context', 'user']);
            }

            return view('auth.login', [
                'showRecaptcha' => false,
                'tenant' => $tenant
            ]);
        }

        // Redirect only when authenticated for this exact context (central).
        if (session('authenticated') && session('auth_context') === $currentContext) {
            return redirect('/dashboard');
        }
        
        return view('auth.login', [
            'showRecaptcha' => $showRecaptcha,
            'tenant' => $tenant
        ]);
    }

    public function login(Request $request)
    {
        // Fail fast when local DB is unhealthy to avoid long login hangs.
        @ini_set('mysql.connect_timeout', (string) env('DB_CONNECT_TIMEOUT', 5));
        @ini_set('default_socket_timeout', (string) env('DB_CONNECT_TIMEOUT', 5));

        if (!$this->isAuthDatabaseResponsive()) {
            return back()->withErrors([
                'email' => 'Login is temporarily unavailable because the database service is not responding. Please restart MySQL and try again.',
            ])->withInput($request->only('email'));
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $currentContext = $this->currentAuthContext();
        $currentTenant = (app()->bound('tenant') && tenant()) ? tenant() : null;

        if (str_starts_with($currentContext, 'tenant:') && !$currentTenant) {
            $currentTenant = $this->resolveTenantFromHost($request);

            if (!$currentTenant) {
                return back()->withErrors([
                    'email' => 'Tenant context is unavailable for this domain. Please verify tenant domain setup.',
                ]);
            }

            app()->instance('tenant', $currentTenant);
            config(['database.connections.tenant' => $currentTenant->getTenantDatabaseConfig()]);
            DB::purge('tenant');
        }

        // Attempt to authenticate against the user table first.
        $user = $currentTenant
            ? User::on('tenant')->where('email', $credentials['email'])->first()
            : User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (strtolower((string) ($user->status ?? 'active')) !== 'active') {
                return back()->withErrors([
                    'email' => 'This account is inactive. Please contact your administrator.',
                ]);
            }

            // Backfill legacy tenant users that were created without tenant_id.
            if ($currentTenant && empty($user->tenant_id)) {
                $user->tenant_id = $currentTenant->tenant_id;
                $user->save();
            }

            if ($currentTenant && $user->tenant_id !== $currentTenant->tenant_id) {
                return back()->withErrors([
                    'email' => 'This account does not belong to the current tenant domain.',
                ]);
            }

            // In tenant context, always trust the active resolved tenant.
            // The user->tenant relation can be unavailable when the user comes from tenant DB.
            $tenant = $currentTenant ?? $user->tenant;
            $plan = 'Basic';
            $features = ['Up to 100 products', 'Inventory tracking', 'Single user'];

            if ($tenant) {
                $tenantPlan = (string) ($tenant->plan ?? data_get($tenant->subscription, 'plan', 'basic'));
                $plan = ucfirst(strtolower($tenantPlan));
                $features = [];
            }

            session([
                'authenticated' => true,
                'auth_context' => $this->currentAuthContext(),
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

            // Keep Laravel's web guard in sync so middleware like Spatie role checks works.
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $submitted = $data['email'];

        // Resolve tenant context if available
        $currentTenant = (app()->bound('tenant') && tenant()) ? tenant() : null;

        // 1) Try tenant connection first
        $user = null;
        if ($currentTenant) {
            try {
                $user = User::on('tenant')->where('email', $submitted)->first();
            } catch (\Throwable $e) {
                Log::warning('Tenant user lookup failed in sendResetLink', ['error' => $e->getMessage()]);
                $user = null;
            }

            if (! $user && strpos($submitted, '@') !== false) {
                try {
                    [$local, $domain] = explode('@', $submitted, 2);
                    $base = preg_replace('/\+.*$/', '', $local);
                    $user = User::on('tenant')->where('email', 'like', $base . '%@' . $domain)->first();
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        // 2) Fall back to central
        if (! $user) {
            $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
            try {
                $user = User::on($centralConn)->where('email', $submitted)->first();
            } catch (\Throwable $e) {
                try {
                    $user = User::where('email', $submitted)->first();
                } catch (\Throwable $inner) {
                    $user = null;
                }
            }
        }

        // 3) If central user belongs to tenant, copy into tenant DB so tenant flows work
        if (! $user && $currentTenant) {
            try {
                $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
                $centralUser = User::on($centralConn)->where('email', $submitted)->first();
                if ($centralUser && ! empty($centralUser->tenant_id) && $centralUser->tenant_id === $currentTenant->tenant_id) {
                    $now = now();
                    $tenantValues = [
                        'tenant_id' => $currentTenant->tenant_id,
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

                    $user = User::on('tenant')->where('email', $centralUser->email)->first();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to copy central user to tenant in sendResetLink', ['email' => $submitted, 'error' => $e->getMessage()]);
            }
        }

        if (! $user) {
            return back()->with('status', 'If an account with that email exists, a password reset link has been generated.');
        }

        // Generate token and store in central and tenant (when applicable)
        $plainToken = Str::random(64);
        $hashed = Hash::make($plainToken);
        try {
            $encrypted = encrypt($plainToken);
        } catch (\Throwable $e) {
            $encrypted = null;
        }

        $centralValues = [
            'token' => $hashed,
            'created_at' => now(),
        ];

        try {
            if (Schema::hasTable('password_reset_tokens') && Schema::hasColumn('password_reset_tokens', 'token_encrypted') && $encrypted !== null) {
                $centralValues['token_encrypted'] = $encrypted;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        DB::table('password_reset_tokens')->updateOrInsert([
            'email' => $user->email,
        ], $centralValues);

        if ($currentTenant) {
            try {
                $tenantValues = [
                    'token' => $hashed,
                    'created_at' => now(),
                ];

                try {
                    if (Schema::connection('tenant')->hasTable('password_reset_tokens') && Schema::connection('tenant')->hasColumn('password_reset_tokens', 'token_encrypted') && $encrypted !== null) {
                        $tenantValues['token_encrypted'] = $encrypted;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

                DB::connection('tenant')->table('password_reset_tokens')->updateOrInsert([
                    'email' => $user->email,
                ], $tenantValues);
            } catch (\Throwable $e) {
                Log::warning('Failed to write tenant password reset token', ['error' => $e->getMessage()]);
            }
        }

        $tenantHost = $currentTenant->domain ?? $request->getHost();
        $scheme = $request->getScheme() ?: 'https';
        $port = (int) $request->getPort();
        if ($port && ! in_array($port, [80, 443], true)) {
            $hostWithPort = $tenantHost . ':' . $port;
        } else {
            $hostWithPort = $tenantHost;
        }
        $resetUrl = $scheme . '://' . $hostWithPort . '/reset-password/' . $plainToken . '?email=' . urlencode($user->email);

        try {
            Mail::send('emails.password-reset', ['user' => $user, 'resetUrl' => $resetUrl], function ($message) use ($user) {
                $message->to($user->email)->subject('Reset your Meat Shop POS password');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset email', ['email' => $user->email, 'error' => $e->getMessage()]);
        }

        return back()->with('status', 'If an account with that email exists, a password reset link has been sent.');
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Ensure tenant context is bound if the request host belongs to a tenant.
        if (! (app()->bound('tenant') && tenant())) {
            $resolved = $this->resolveTenantFromHost($request);
            if ($resolved) {
                app()->instance('tenant', $resolved);
                config(['database.connections.tenant' => $resolved->getTenantDatabaseConfig()]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
            }
        }

        // Try the active/default connection first (tenant when bound).
        try {
            if (app()->bound('tenant') && tenant()) {
                $tokenRecord = DB::connection('tenant')->table('password_reset_tokens')
                    ->where('email', $data['email'])
                    ->first();
            } else {
                $tokenRecord = DB::table('password_reset_tokens')
                    ->where('email', $data['email'])
                    ->first();
            }
        } catch (\Throwable $e) {
            // If the preferred connection/table lookup failed for any reason,
            // fall back to the default DB connection.
            try {
                $tokenRecord = DB::table('password_reset_tokens')
                    ->where('email', $data['email'])
                    ->first();
            } catch (\Throwable $inner) {
                $tokenRecord = null;
            }
        }

        // If we're handling a tenant request and the token wasn't found in the
        // tenant DB, try the central DB as a fallback (tokens may have been
        // generated from the central site).
        if (! $tokenRecord && app()->bound('tenant') && tenant()) {
            $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
            try {
                $centralRecord = DB::connection($centralConn)->table('password_reset_tokens')
                    ->where('email', $data['email'])
                    ->first();
                if ($centralRecord) {
                    $tokenRecord = $centralRecord;
                    // For diagnostics below, prefer to indicate where the token came from
                    // by temporarily setting the database.default value for the scope.
                    config(['_password_reset_token_source_connection' => $centralConn]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to query central password_reset_tokens as fallback', ['error' => $e->getMessage()]);
            }
        }

        // Debug diagnostics to help diagnose expired/invalid tokens
        try {
            $connectionName = (string) (config('_password_reset_token_source_connection') ?? config('database.default'));
            $dbName = (string) (config("database.connections.{$connectionName}.database") ?? config('database.connections.tenant.database'));

            if (! $tokenRecord) {
                Log::debug('Password reset token not found for email on connection.', [
                    'email' => $data['email'],
                    'connection' => $connectionName,
                    'database' => $dbName,
                ]);
                if (config('app.debug')) {
                    return back()->withErrors(['email' => 'Invalid or expired reset token.', 'debug' => "token_not_found|conn={$connectionName}|db={$dbName}"])->withInput();
                }
                return back()->withErrors(['email' => 'Invalid or expired reset token.'])->withInput();
            }

            $createdAt = $tokenRecord->created_at;
            $diff = now()->diffInMinutes($createdAt);
            $isExpired = $diff > 60;

            // Additional diagnostic logging to inspect token strings and lengths.
            $givenToken = (string) ($data['token'] ?? '');
            $dbTokenHash = (string) ($tokenRecord->token ?? '');
            $givenLen = strlen($givenToken);
            $dbLen = strlen($dbTokenHash);

            // Use both Hash::check and raw password_verify as diagnostics.
            $hashCheck = Hash::check($givenToken, $dbTokenHash);
            $pv = function_exists('password_verify') ? password_verify($givenToken, $dbTokenHash) : null;

            Log::debug('Password reset token check', [
                'email' => $data['email'],
                'connection' => $connectionName,
                'database' => $dbName,
                'created_at' => $createdAt,
                'minutes_since' => $diff,
                'given_token' => $givenToken,
                'given_len' => $givenLen,
                'db_token_hash' => $dbTokenHash,
                'db_hash_len' => $dbLen,
                'hash_check' => $hashCheck,
                'password_verify' => $pv,
            ]);

            $isTokenValid = (bool) $hashCheck;

            // Fallback attempts: try common decoding/transformations of the token
            if (! $isTokenValid) {
                $attempts = [];
                $raw = $givenToken;
                $attempts[] = urldecode($raw);
                $attempts[] = rawurldecode($raw);
                $attempts[] = trim($raw);
                $attempts[] = str_replace(' ', '+', $raw);
                $attempts[] = rtrim($raw, '\\r\\n');

                foreach ($attempts as $alt) {
                    if ($alt === $raw) continue;
                    $check = Hash::check($alt, $dbTokenHash);
                    if ($check) {
                        $isTokenValid = true;
                        Log::debug('Password reset token matched after transformation', ['email' => $data['email'], 'transformed' => $alt]);
                        break;
                    }
                }
            }
            
            // Final fallback: if we stored an encrypted copy of the original token,
            // decrypt it and compare for exact match. This avoids failures caused by
            // hashing differences while keeping the token secret in DB.
            if (! $isTokenValid && ! empty($tokenRecord->token_encrypted)) {
                try {
                    $decrypted = decrypt($tokenRecord->token_encrypted);
                    if ($decrypted === $givenToken) {
                        $isTokenValid = true;
                        Log::debug('Password reset token matched using encrypted fallback', ['email' => $data['email']]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to decrypt stored reset token', ['email' => $data['email'], 'error' => $e->getMessage()]);
                }
            }
            if ($isExpired || ! $isTokenValid) {
                if (config('app.debug')) {
                    return back()->withErrors(['email' => 'Invalid or expired reset token.', 'debug' => "expired={$isExpired}|valid={$isTokenValid}|minutes={$diff}"])->withInput();
                }
                return back()->withErrors(['email' => 'Invalid or expired reset token.'])->withInput();
            }
        } catch (\Throwable $e) {
            Log::error('Error during password reset diagnostic check', ['error' => $e->getMessage()]);
        }

        // Prefer tenant DB lookup when handling a tenant-scoped reset. This
        // ensures we find users that live only in the tenant database.
        $user = null;
        if (app()->bound('tenant') && tenant()) {
            try {
                $user = User::on('tenant')->where('email', $data['email'])->first();
            } catch (\Throwable $e) {
                Log::warning('Tenant user lookup failed during reset; falling back', ['error' => $e->getMessage()]);
                $user = null;
            }

            // Normalized plus-address fallback on tenant connection
            if (! $user) {
                try {
                    if (strpos($data['email'], '@') !== false) {
                        [$local, $domain] = explode('@', $data['email'], 2);
                        $normalized = preg_replace('/\+.*$/', '', $local);
                        $alt = User::on('tenant')
                            ->where('email', 'like', $normalized . '+%@' . $domain)
                            ->orWhere('email', $data['email'])
                            ->first();
                        if ($alt) {
                            $user = $alt;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Alt tenant email lookup failed during reset', ['email' => $data['email'], 'error' => $e->getMessage()]);
                }
            }
        } else {
            $user = User::where('email', $data['email'])->first();
        }

        // If the tenant user is missing during a tenant-scoped reset, attempt
        // to copy the central user into the tenant DB so the reset can complete.
        if (! $user && app()->bound('tenant') && tenant()) {
            try {
                $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
                $centralUser = User::on($centralConn)->where('email', $data['email'])->first();
                if ($centralUser) {
                    // Only copy the central user into the tenant DB when the central
                    // record explicitly belongs to that tenant (tenant_id matches).
                    // This prevents backup/contact emails from becoming tenant logins.
                    if (! empty($centralUser->tenant_id) && $centralUser->tenant_id === tenant()->tenant_id) {
                        $tenant = tenant();
                        $now = now();
                        $tenantValues = [
                        'tenant_id' => $tenant->tenant_id,
                        'username' => $centralUser->username ?? (string) Str::slug($centralUser->email, '_'),
                        'name' => $centralUser->name ?? '',
                        'email' => $centralUser->email,
                        // Preserve the central hashed password so the user can sign in immediately.
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

                        // Reload the user from tenant DB for subsequent steps.
                        $user = User::on('tenant')->where('email', $data['email'])->first();
                    } else {
                        Log::debug('Central user exists but tenant_id does not match; not copying into tenant', [
                            'email' => $data['email'],
                            'central_tenant_id' => $centralUser->tenant_id ?? null,
                            'resolved_tenant_id' => tenant()->tenant_id,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to copy central user to tenant DB during password reset', ['email' => $data['email'], 'error' => $e->getMessage()]);
            }
        }

        if (! $user) {
            Log::debug('Password reset: user lookup failed', [
                'email' => $data['email'],
                'tenant_bound' => app()->bound('tenant') && tenant() ? true : false,
                'tenant_id' => app()->bound('tenant') && tenant() ? tenant()->tenant_id : null,
                'token_source' => config('_password_reset_token_source_connection') ?? config('database.default'),
            ]);

            return back()->withErrors(['email' => 'No user found for this reset request.'])->withInput();
        }

        $user->password = Hash::make($data['password']);
        $user->login_attempts = 0;
        $user->lock_until = null;
        $user->save();

        // Delete the reset token from the same connection where it was stored.
        $tokenSource = (string) (config('_password_reset_token_source_connection') ?? config('database.default'));
        try {
            DB::connection($tokenSource)->table('password_reset_tokens')->where('email', $data['email'])->delete();
        } catch (\Throwable $e) {
            // Fallback: try default connection
            try {
                DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            } catch (\Throwable $inner) {
                Log::warning('Failed to delete reset token after password reset', ['email' => $data['email'], 'error' => $inner->getMessage()]);
            }
        }

        return redirect('/login')->with('status', 'Password updated successfully. You can now sign in.');
    }


    public function logout()
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }

    private function currentAuthContext(): string
    {
        $host = strtolower((string) request()->getHost());
        $centralDomains = array_map('strtolower', (array) config('tenancy.central_domains', []));

        if ($host !== '' && !in_array($host, $centralDomains, true)) {
            return 'tenant:' . $host;
        }

        return 'central';
    }

    private function resolveTenantFromHost(Request $request): ?Tenant
    {
        $host = strtolower((string) $request->getHost());

        if ($host === '') {
            return null;
        }

        if (Schema::hasTable('domains')) {
            $domain = Domain::query()->where('domain', $host)->first();

            if ($domain && $domain->tenant) {
                return $domain->tenant;
            }
        }

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'domain')) {
            return Tenant::query()->where('domain', $host)->first();
        }

        return null;
    }

    private function isAuthDatabaseResponsive(): bool
    {
        $defaultConnection = (string) config('database.default', 'mysql');

        if (!in_array($defaultConnection, ['mysql', 'mariadb'], true)) {
            return true;
        }

        if (!function_exists('mysqli_init')) {
            return true;
        }

        $host = (string) config("database.connections.{$defaultConnection}.host", '127.0.0.1');
        $port = (int) config("database.connections.{$defaultConnection}.port", 3306);
        $username = (string) config("database.connections.{$defaultConnection}.username", 'root');
        $password = (string) config("database.connections.{$defaultConnection}.password", '');
        $database = (string) config("database.connections.{$defaultConnection}.database", '');
        $timeout = max(1, (int) env('DB_CONNECT_TIMEOUT', 5));

        if (function_exists('mysqli_report') && defined('MYSQLI_REPORT_OFF')) {
            mysqli_report(MYSQLI_REPORT_OFF);
        }

        $mysqli = mysqli_init();

        if ($mysqli === false) {
            return false;
        }

        try {
            mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
            mysqli_options($mysqli, MYSQLI_OPT_READ_TIMEOUT, $timeout);

            $connected = @mysqli_real_connect(
                $mysqli,
                $host,
                $username,
                $password,
                $database,
                $port
            );

            return $connected === true;
        } catch (\Throwable $e) {
            return false;
        } finally {
            @mysqli_close($mysqli);
        }
    }
}
