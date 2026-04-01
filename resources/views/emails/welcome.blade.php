<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $businessName }}</title>
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
        .password-box {
            background-color: #f8f9fa;
            border: 2px dashed #dc3545;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border-radius: 8px;
        }
        .password {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
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
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🥩 Meat Shop POS</div>
            <h2>Welcome to {{ $businessName }}!</h2>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            
            <p>Your account has been successfully created for <strong>{{ $businessName }}</strong> with the role of <strong>{{ ucfirst($userRole) }}</strong>.</p>
            
            <div class="security-notice">
                <strong>🔒 Security Information:</strong><br>
                For your security, we have generated a temporary password below. Please change it after your first login.
            </div>
            
            <div class="password-box">
                <p>Your temporary password is:</p>
                <div class="password">{{ $password }}</div>
            </div>
            
            <p><strong>Login Instructions:</strong></p>
            <ol>
                <li>Visit: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></li>
                <li>Use your email: <strong>{{ $email }}</strong></li>
                <li>Use the temporary password provided above</li>
                <li>Change your password immediately after login</li>
            </ol>
            
            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Your Account</a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Important:</strong> Never share your password with anyone. If you didn't request this account, please contact support immediately.</p>
            <p>&copy; {{ date('Y') }} {{ $businessName }} - Meat Shop POS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
