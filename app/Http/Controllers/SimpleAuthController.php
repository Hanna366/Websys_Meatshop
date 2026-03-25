<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SimpleAuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $currentContext = $this->currentAuthContext();

        if ($request->boolean('force_login')) {
            session()->forget(['authenticated', 'auth_context', 'user']);
            return view('auth.login');
        }

        // Tenant domains should always show the login screen when opened.
        if (str_starts_with($currentContext, 'tenant:')) {
            if (session('authenticated') && session('auth_context') === $currentContext) {
                session()->forget(['authenticated', 'auth_context', 'user']);
            }

            return view('auth.login');
        }

        // Redirect only when authenticated for this exact context (central).
        if (session('authenticated') && session('auth_context') === $currentContext) {
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
            if (app()->bound('tenant')) {
                $currentTenant = tenant();

                if (!$currentTenant || $user->tenant_id !== $currentTenant->tenant_id) {
                    return back()->withErrors([
                        'email' => 'This account does not belong to the current tenant domain.',
                    ]);
                }
            }

            $tenant = $user->tenant;
            $plan = 'Basic';
            $features = ['Up to 100 products', 'Inventory tracking', 'Single user'];

            if ($tenant) {
                $plan = ucfirst($tenant->plan ?? 'basic');
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
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }

    private function currentAuthContext(): string
    {
        if (app()->bound('tenant') && tenant()) {
            return 'tenant:' . tenant()->tenant_id;
        }

        return 'central';
    }
}
