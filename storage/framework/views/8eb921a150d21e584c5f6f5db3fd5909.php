<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to MeatShopPOS</title>
</head>
<body style="margin:0; padding:0; background:#f6f7fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#111827; color:#ffffff; padding:24px;">
                            <h1 style="margin:0; font-size:22px; line-height:1.3;">Your MeatShopPOS Tenant Is Ready</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px; font-size:15px;">Hi <?php echo e($adminName); ?>,</p>
                            <p style="margin:0 0 14px; font-size:15px; line-height:1.6;">
                                Your tenant <strong><?php echo e($businessName); ?></strong> has been provisioned successfully.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 16px; border-collapse:collapse;">
                                <tr>
                                    <td style="padding:8px 0; font-size:14px;"><strong>Admin Email:</strong> <?php echo e($adminEmail); ?></td>
                                </tr>
                                <?php if(!empty($generatedPassword)): ?>
                                    <tr>
                                        <td style="padding:8px 0; font-size:14px;"><strong>Temporary Password:</strong> <?php echo e($generatedPassword); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px;"><strong>Plan:</strong> <?php echo e(ucfirst((string) ($plan ?? 'basic'))); ?></td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:14px;"><strong>Login URL</strong></p>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                <a href="<?php echo e($loginUrl); ?>" style="color:#0f766e; text-decoration:none;"><?php echo e($loginUrl); ?></a>
                            </p>

                            <p style="margin:0 0 10px; font-size:14px;"><strong>Getting Started</strong></p>
                            <ol style="margin:0 0 16px 20px; padding:0; font-size:14px; line-height:1.8;">
                                <li>Open the login URL above and sign in with your admin email@if(!empty($generatedPassword)) and the temporary password@endif.</li>
                                <?php if(!empty($passwordSetupUrl)): ?>
                                    <li>If this is your first login or you forgot your password, set/reset it here: <a href="<?php echo e($passwordSetupUrl); ?>" style="color:#0f766e; text-decoration:none;"><?php echo e($passwordSetupUrl); ?></a></li>
                                <?php else: ?>
                                    <li>If this is your first login, use the password reset flow to set your password.</li>
                                <?php endif; ?>
                                <li>Review your catalog, inventory, and team settings to finish onboarding.</li>
                            </ol>

                            <?php if(!empty($generatedPassword)): ?>
                                <p style="margin:0; font-size:13px; color:#6b7280; line-height:1.6;">
                                    This temporary password was auto-generated because no password was provided during tenant creation. Please change it after first login.
                                </p>
                            <?php else: ?>
                                <p style="margin:0; font-size:13px; color:#6b7280; line-height:1.6;">
                                    For security, we never send plaintext passwords by email unless an auto-generated temporary password was requested.
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\Users\OWNER\Documents\webs\meatshop\resources\views/emails/tenant-onboarding.blade.php ENDPATH**/ ?>