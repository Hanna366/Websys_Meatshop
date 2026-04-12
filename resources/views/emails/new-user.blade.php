<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your account</title>
</head>
<body>
    <h2>Welcome to {{ $tenantName }}</h2>

    <p>Hello {{ $userName }},</p>

    <p>An account has been created for you on {{ $tenantName }}. You can sign in using the following credentials:</p>

    <ul>
        <li><strong>Email:</strong> {{ $userEmail }}</li>
        <li><strong>Password:</strong> {{ $generatedPassword }}</li>
    </ul>

    <p>For security, we recommend you change your password after signing in.</p>

    <p>If you did not expect this email, please contact your administrator.</p>

    <p>Thanks,<br/>The {{ $tenantName }} team</p>
</body>
</html>
