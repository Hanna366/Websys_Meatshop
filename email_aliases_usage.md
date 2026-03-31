# Email Aliases Usage Guide

## Overview
Your Meat Shop POS system now automatically generates business email aliases using the format: `+(business-name)@gmail.com` for production and `+(business-name).localhost` for local development.

## Email Aliases Generated

For a business named "Meat Shop POS", the system will generate:

### Production Environment:
- **Main Business**: `+meatshoppos@gmail.com`
- **Owner**: `+meatshoppos-owner@gmail.com`
- **Manager**: `+meatshoppos-manager@gmail.com`
- **Cashier**: `+meatshoppos-cashier@gmail.com`
- **Inventory Staff**: `+meatshoppos-inventory@gmail.com`
- **Support**: `+meatshoppos-support@gmail.com`
- **Billing**: `+meatshoppos-billing@gmail.com`
- **Info**: `+meatshoppos-info@gmail.com`

### Local Development Environment:
- **Main Business**: `+meatshoppos.localhost`
- **Owner**: `+meatshoppos-owner.localhost`
- **Manager**: `+meatshoppos-manager.localhost`
- **Cashier**: `+meatshoppos-cashier.localhost`
- **Inventory Staff**: `+meatshoppos-inventory.localhost`
- **Support**: `+meatshoppos-support.localhost`
- **Billing**: `+meatshoppos-billing.localhost`
- **Info**: `+meatshoppos-info.localhost`

## How to Use in Code

```php
use App\Helpers\EmailHelper;

// Get specific role email (auto-detects environment)
$ownerEmail = EmailHelper::getBusinessEmail('owner', 'Meat Shop POS');
// Local: +meatshoppos-owner.localhost
// Production: +meatshoppos-owner@gmail.com

// Get all email aliases (auto-detects environment)
$allEmails = EmailHelper::getAllBusinessEmails('Meat Shop POS');
// Returns array with appropriate domain for current environment
```

## Implementation Details

1. **UserSeeder**: Automatically uses email aliases for all created users
2. **TenantService**: Uses email aliases for admin user creation
3. **Format**: Business name is cleaned (spaces, dashes, underscores removed)
4. **Domain**: Uses @gmail.com for production, .localhost for local development
5. **Auto-Detection**: Environment is automatically detected and applied

## Environment Auto-Switching

The system automatically switches between:
- **Local Development**: When `APP_ENV=local`
- **Production**: When `APP_ENV=production` or any other value

## Benefits

- **Professional**: Consistent email format across all users
- **Organized**: Easy to identify role by email address
- **Scalable**: Works for any business name
- **Flexible**: Can be modified to use different domains
- **Development-Friendly**: Automatic localhost switching for local testing

## Customization

### Change Domains
Edit `app/Helpers/EmailHelper.php`:

```php
// Change production domain
'main' => "{$cleanName}@yourdomain.com",

// Change local domain  
if (app()->environment('local')) {
    $aliases[$key] = str_replace('@gmail.com', '.local', $email);
}
```

### Add New Roles
```php
$aliases = [
    // ... existing roles
    'supervisor' => "+{$cleanName}-supervisor@gmail.com",
    'accountant' => "+{$cleanName}-accountant@gmail.com",
];
```

## Setup Required

### For Production:
1. **Create Gmail accounts** with the `+` aliases
2. **Set up email forwarding** to your main business email
3. **Configure email sending** in your `.env` file
4. **Test email functionality**

### For Local Development:
1. **Local email testing** works automatically with `.localhost` domain
2. **No real emails** will be sent in local environment
3. **Email logging** can be enabled for debugging

The aliases will be automatically used when:
- Creating new tenants
- Running database seeders
- Generating admin users
- Any system email generation

## Example Usage

```bash
# In local development
php artisan db:seed
# Creates users with .localhost emails

# In production  
php artisan db:seed
# Creates users with @gmail.com emails
