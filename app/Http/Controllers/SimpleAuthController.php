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

        $userQuery = User::where('email', $data['email']);

        if (app()->bound('tenant') && tenant()) {
            $userQuery->where('tenant_id', tenant()->tenant_id);
        }

        $user = $userQuery->first();

        if ($user) {
            $plainToken = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($plainToken),
                    'created_at' => now(),
                ]
            );

            $resetUrl = url('/reset-password/' . $plainToken) . '?email=' . urlencode($user->email);
            try {
                Mail::send('emails.password-reset', [
                    'user' => $user,
                    'resetUrl' => $resetUrl,
                ], function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Reset your Meat Shop POS password');
                });

                return back()->with('status', 'If an account with that email exists, a password reset link has been sent.');
            } catch (\Throwable $e) {
                Log::error('Failed to send password reset email.', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            if (config('app.debug')) {
                return back()->with([
                    'status' => 'Could not send email. Use the development reset link below.',
                    'reset_link' => $resetUrl,
                ]);
            }
        }

        return back()->with('status', 'If an account with that email exists, a password reset link has been generated.');
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

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (! $tokenRecord) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.'])->withInput();
        }

        $isExpired = now()->diffInMinutes($tokenRecord->created_at) > 60;
        $isTokenValid = Hash::check($data['token'], $tokenRecord->token);

        if ($isExpired || ! $isTokenValid) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.'])->withInput();
        }

        $userQuery = User::where('email', $data['email']);

        if (app()->bound('tenant') && tenant()) {
            $userQuery->where('tenant_id', tenant()->tenant_id);
        }

        $user = $userQuery->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No user found for this reset request.'])->withInput();
        }

        $user->password = Hash::make($data['password']);
        $user->login_attempts = 0;
        $user->lock_until = null;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

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
