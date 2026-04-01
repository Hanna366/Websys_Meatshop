<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Account Ready - {{ $businessName }}</title>
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
            border-bottom: 2px solid #28a745;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .content {
            margin-bottom: 30px;
        }
        .success-box {
            background-color: #d4edda;
            border: 2px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border-radius: 8px;
            color: #155724;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .detail-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
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
        .next-steps {
            background-color: #e2e3e5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .next-steps h4 {
            margin-top: 0;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🥩 Meat Shop POS</div>
            <h2>🎉 Your Account is Ready!</h2>
        </div>
        
        <div class="content">
            <div class="success-box">
                <h3>Congratulations! 🎊</h3>
                <p>Your Meat Shop POS account for <strong>{{ $businessName }}</strong> has been successfully created and is ready to use.</p>
            </div>
            
            <h3>Account Details:</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Business Name</div>
                    <div>{{ $businessName }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Login Email</div>
                    <div>{{ $email }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Plan</div>
                    <div>{{ ucfirst($tenantDetails['plan'] ?? 'Basic') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div>✅ Active</div>
                </div>
            </div>
            
            <div class="next-steps">
                <h4>Next Steps:</h4>
                <ol>
                    <li><strong>Login</strong> to your account using the button below</li>
                    <li><strong>Change Password</strong> after your first login for security</li>
                    <li><strong>Configure</strong> your business settings</li>
                    <li><strong>Add Products</strong> to your inventory</li>
                    <li><strong>Set Up Users</strong> for your team</li>
                </ol>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Your Account</a>
            </div>
            
            <p><strong>Need Help?</strong></p>
            <ul>
                <li>📧 Email: support@meatshop.com</li>
                <li>📞 Phone: +1-800-MEATSHOP</li>
                <li>📚 Documentation: docs.meatshop.com</li>
            </ul>
        </div>
        
        <div class="footer">
            <p><strong>Welcome aboard!</strong> We're excited to help you grow your meat shop business.</p>
            <p>&copy; {{ date('Y') }} {{ $businessName }} - Meat Shop POS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
