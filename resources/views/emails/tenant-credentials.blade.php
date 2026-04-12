<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Access details — {{ $tenantName }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background:#f6f7fb; margin:0; padding:24px; color:#111827;">
  <table role="presentation" width="100%" style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; border:1px solid #e6e9ef; overflow:hidden;">
    <tr>
      <td style="padding:20px 24px; background:linear-gradient(90deg,#a41245,#f63470); color:#fff;">
        <h1 style="margin:0; font-size:20px; font-weight:700;">Your MeatShopPOS tenant is ready</h1>
        <p style="margin:6px 0 0; opacity:0.9;">{{ $tenantName }}</p>
      </td>
    </tr>
    <tr>
      <td style="padding:20px 24px;">
        <p style="margin:0 0 12px;">Hi {{ $adminName }},</p>

        <p style="margin:0 0 12px; line-height:1.5;">Your tenant <strong>{{ $tenantName }}</strong> has been created. Below are the access details to sign in and finish onboarding.</p>

        <table role="presentation" width="100%" style="margin:12px 0 18px; border-collapse:collapse;">
          <tr>
            <td style="padding:8px 0; font-size:14px;"><strong>Admin Email:</strong> {{ $adminEmail }}</td>
          </tr>
          <tr>
            <td style="padding:8px 0; font-size:14px;"><strong>Plan:</strong> {{ ucfirst((string) ($plan ?? 'basic')) }}</td>
          </tr>
          @if(!empty($generatedPassword))
          <tr>
            <td style="padding:8px 0; font-size:14px;"><strong>Temporary password:</strong> <code style="background:#f3f4f6;padding:4px 8px;border-radius:6px;">{{ $generatedPassword }}</code></td>
          </tr>
          @endif
        </table>

        <p style="margin:0 0 6px; font-weight:600;">Login</p>
        <p style="margin:0 0 12px;"><a href="{{ $loginUrl }}" style="color:#0f766e; text-decoration:none;">{{ $loginUrl }}</a></p>

        <p style="margin:0 0 12px;">For security we recommend you set a new password immediately. Use the link below to set or reset your password:</p>
        <p style="margin:0 0 18px;"><a href="{{ $resetUrl }}" style="display:inline-block;padding:10px 16px;border-radius:8px;background:linear-gradient(90deg,#a41245,#f63470);color:#fff;text-decoration:none;font-weight:600;">Set / Reset Password</a></p>

        <p style="margin:0 0 8px; color:#6b7280; font-size:13px;">Notes:</p>
        <ul style="margin:0 0 12px 18px; color:#6b7280; font-size:13px;">
          <li>We included a temporary password for demo/educational purposes only.</li>
          <li>Best practice: change your password on first login and avoid using emailed passwords in production.</li>
        </ul>

        <p style="margin:0; font-size:13px; color:#6b7280;">If you didn't request this tenant or need help, reply to this email or contact support.</p>
      </td>
    </tr>
  </table>
</body>
</html>
