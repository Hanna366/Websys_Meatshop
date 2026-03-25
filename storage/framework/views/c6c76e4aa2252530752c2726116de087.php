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
            <p style="margin-top: 0;">Hi <?php echo e($user->name ?: $user->email); ?>,</p>
            <p>We received a request to reset your password. Click the button below to continue.</p>

            <p style="margin: 22px 0;">
                <a href="<?php echo e($resetUrl); ?>" style="display: inline-block; padding: 10px 16px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px;">Reset Password</a>
            </p>

            <p>This link will expire in 60 minutes.</p>
            <p>If you did not request this, you can safely ignore this email.</p>

            <p style="font-size: 13px; color: #6b7280; word-break: break-all;">If the button does not work, copy and paste this URL into your browser:<br><?php echo e($resetUrl); ?></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views\emails\password-reset.blade.php ENDPATH**/ ?>