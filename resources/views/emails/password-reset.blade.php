<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background: #f3f4f6;">
    <div style="max-width: 560px; margin: 24px auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
        <div style="background: #1f3a8a; color: #ffffff; padding: 18px 22px;">
            <h2 style="margin: 0; font-size: 20px;">Meat Shop POS</h2>
        </div>

        <div style="padding: 22px;">
            <p style="margin-top: 0;">Hi {{ $userName ?? ($user->name ?? $user->email ?? $email) }},</p>
            <p>We received a request to reset your password. Click the button below to continue.</p>

            <p style="margin: 22px 0;">
                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 10px 16px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px;">Reset Password</a>
            </p>

            <p>This link will expire in 60 minutes.</p>
            <p>If you did not request this, you can safely ignore this email.</p>

            @if (!empty($altEmails) && is_array($altEmails))
                <p style="font-size: 13px; color: #6b7280;">This reset link was requested for <strong>{{ $email }}</strong>. If you have another email address for this account, you can also use one of the following addresses to access the reset form:</p>
                <ul style="font-size: 13px; color: #6b7280;">
                    @foreach ($altEmails as $alt)
                        <li>{{ $alt }}</li>
                    @endforeach
                </ul>
            @endif

            <p style="font-size: 13px; color: #6b7280; word-break: break-all;">If the button does not work, copy and paste this URL into your browser:<br>{{ $resetUrl }}</p>
        </div>
    </div>
</body>
</html>
