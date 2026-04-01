<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - {{ $businessName }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #dc3545;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
        .security-notice {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            color: #721c24;
        }
        .reset-code {
            background-color: #f8f9fa;
            border: 2px dashed #dc3545;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🥩 Meat Shop POS</div>
            <h2>Password Reset Request</h2>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            
            <p>We received a request to reset the password for your account at <strong>{{ $businessName }}</strong>.</p>
            
            <div class="security-notice">
                <strong>⚠️ Security Notice:</strong><br>
                If you didn't request this password reset, please ignore this email. Your password will remain unchanged.
            </div>
            
            <p><strong>Password Reset Code:</strong></p>
            <div class="reset-code">{{ $resetToken }}</div>
            
            <p><strong>Reset Instructions:</strong></p>
            <ol>
                <li>Click the button below to reset your password</li>
                <li>Or visit: <a href="{{ $resetUrl }}">{{ $resetUrl }}</a></li>
                <li>Enter the reset code: <strong>{{ $resetToken }}</strong></li>
                <li>Create your new password</li>
            </ol>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </div>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This reset code will expire in 1 hour for security reasons</li>
                <li>Never share this code with anyone</li>
                <li>If you didn't request this reset, please contact support immediately</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $businessName }} - Meat Shop POS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
