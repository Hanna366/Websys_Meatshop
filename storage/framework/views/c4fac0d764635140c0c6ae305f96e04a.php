<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Test</title>
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
            border-bottom: 2px solid #007bff;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .content {
            margin-bottom: 30px;
        }
        .test-info {
            background-color: #d1ecf1;
            border: 2px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border-radius: 8px;
            color: #004085;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status.success {
            background-color: #28a745;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🥩 Meat Shop POS</div>
            <h2>📧 Email Configuration Test</h2>
        </div>
        
        <div class="content">
            <div class="test-info">
                <h3>✅ Email Test Successful!</h3>
                <p>Your email configuration is working correctly.</p>
                
                <p><strong>Test Email Sent To:</strong><br>
                <?php echo e($testEmail); ?></p>
                
                <p><strong>Test Timestamp:</strong><br>
                <?php echo e($timestamp); ?></p>
                
                <div class="status success">
                    CONFIGURATION OK
                </div>
            </div>
            
            <p><strong>What This Means:</strong></p>
            <ul>
                <li>✅ SMTP server connection is working</li>
                <li>✅ Email authentication is successful</li>
                <li>✅ Email delivery is functional</li>
                <li>✅ Your application can send password reset emails</li>
                <li>✅ Your application can send welcome emails</li>
            </ul>
            
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Test user registration to receive welcome emails</li>
                <li>Test password reset functionality</li>
                <li>Check email logs for delivery status</li>
            </ol>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo e(date('Y')); ?> Meat Shop POS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/emails/test.blade.php ENDPATH**/ ?>