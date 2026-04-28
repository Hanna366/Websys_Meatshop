<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your {{ $tenantName }} account is ready</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .credentials-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-row {
            display: flex;
            margin-bottom: 12px;
        }
        .credential-row:last-child {
            margin-bottom: 0;
        }
        .credential-label {
            font-weight: 600;
            color: #111827;
            min-width: 140px;
        }
        .credential-value {
            color: #374151;
        }
        .password {
            font-family: 'Courier New', monospace;
            background-color: #e5e7eb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .role-badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .button {
            display: inline-block;
            background-color: #059669;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #047857;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 32px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
        .security-note {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        a {
            color: #059669;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your {{ $tenantName }} account is ready</h1>
            <p>{{ $tenantName }}</p>
        </div>

        <div class="content">
            <p class="greeting">Hi {{ $userName }},</p>

            <p>Your account for <strong>{{ $tenantName }}</strong> has been created. Below are the access details to sign in and start using the system.</p>

            <div class="credentials-box">
                <div class="credential-row">
                    <span class="credential-label">Your Email:</span>
                    <span class="credential-value"><a href="mailto:{{ $userEmail }}">{{ $userEmail }}</a></span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Username:</span>
                    <span class="credential-value">{{ $username }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Role:</span>
                    <span class="credential-value"><span class="role-badge">{{ $role }}</span></span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Temporary password:</span>
                    <span class="credential-value"><span class="password">{{ $generatedPassword }}</span></span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Your Account</a>
            </div>

            <div class="security-note">
                <strong>Security Note:</strong> For security we recommend you set a new password immediately. Use the link below to set or reset your password for your {{ $tenantName }} account:
            </div>

            <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>

            <p>Welcome to the team! If you have any questions, please contact your administrator.</p>
        </div>

        <div class="footer">
            <p>This email was sent automatically. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
