<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We cannot find a user with that email address.',
        ]);

        $user = User::where('email', $request->email)->first();
        $resetToken = Str::random(60);

        // Store reset token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $resetToken,
                'created_at' => now(),
                'expires_at' => now()->addHour(),
            ]
        );

        // Send password reset email
        $businessName = 'Meat Shop POS';
        if ($user->tenant_id && function_exists('tenant')) {
            $tenant = \App\Models\Tenant::where('tenant_id', $user->tenant_id)->first();
            if ($tenant) {
                $businessName = $tenant->business_name;
            }
        }

        $emailResult = EmailService::sendPasswordReset(
            $request->email,
            $businessName,
            $resetToken
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
        $resetToken = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

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

        $resetToken = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetToken) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password),
                'login_attempts' => 0,
                'lock_until' => null,
            ]);

            // Delete the reset token
            DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->delete();

            return redirect('/login')->with('success', 'Password has been reset successfully!');
        }

        return back()->withErrors(['email' => 'Unable to reset password. Please try again.']);
    }
}
