<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Send password reset email (central-only)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $deliveryEmail = (string) $request->email;
        $centralConn = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));

        $accountUser = User::on($centralConn)->where(function ($q) use ($deliveryEmail, $centralConn) {
            $q->where('email', $deliveryEmail);
            try {
                if (\Illuminate\Support\Facades\Schema::connection($centralConn)->hasTable('users') && \Illuminate\Support\Facades\Schema::connection($centralConn)->hasColumn('users', 'recovery_email')) {
                    $q->orWhere('recovery_email', $deliveryEmail);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        })->first();

        if (! $accountUser) {
            return back()->with('status', 'If an account with that email exists, a password reset link has been generated.');
        }

        $resetToken = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $accountUser->email],
            [
                'token' => $resetToken,
                'created_at' => now(),
                'expires_at' => now()->addHour(),
            ]
        );

        $businessName = config('app.name', 'Meat Shop POS');
        $scheme = $request->getScheme() ?: 'https';
        $hostWithPort = $request->getHost();
        $port = (int) $request->getPort();
        if ($port && ! in_array($port, [80, 443], true)) {
            $hostWithPort .= ':' . $port;
        }
        $resetUrlHost = $scheme . '://' . $hostWithPort;

        $userName = $accountUser->name ?? null;
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name');
        $altEmails = [];

        $emailResult = EmailService::sendPasswordReset(
            $deliveryEmail,
            $businessName,
            $resetToken,
            $resetUrlHost,
            $fromAddress,
            $fromName,
            $userName,
            $altEmails,
            'emails.password-reset'
        );

        if (! $emailResult['success']) {
            Log::error('Failed to send password reset email: ' . $emailResult['error']);
        }

        $message = $emailResult['success']
            ? 'Password reset link has been sent to your email.'
            : 'Failed to send password reset email. Please try again later.';

        return back()->with('status', $message);
    }

    /**
     * Show password reset form with token (central-only)
     */
    public function showResetForm($token)
    {
        $resetToken = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $resetToken) {
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
     * Reset password (central-only)
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetToken = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $resetToken) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        $submittedEmail = (string) $request->email;
        $tokenEmail = (string) ($resetToken->email ?? $submittedEmail);

        try {
            $user = User::where('email', $tokenEmail)->first();
        } catch (\Throwable $e) {
            Log::warning('Central lookup failed during password reset', ['error' => $e->getMessage()]);
            $user = null;
        }

        if (! $user) {
            return back()->withErrors(['email' => 'Unable to reset password. Please try again.']);
        }

        $newHashed = Hash::make($request->password);
        try {
            $user->password = $newHashed;
            $user->login_attempts = 0;
            $user->lock_until = null;
            $user->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to save password on model during reset', ['email' => $user->email, 'error' => $e->getMessage()]);
            return back()->withErrors(['email' => 'Unable to reset password. Please try again.']);
        }

        try {
            DB::table('password_reset_tokens')->where('token', $request->token)->delete();
        } catch (\Throwable $e) {
            Log::warning('Failed to delete password reset token after reset', ['token' => $request->token, 'error' => $e->getMessage()]);
        }

        return redirect('/login')->with('success', 'Password has been reset successfully!');
    }
}
