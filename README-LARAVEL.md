# 🥩 Meat Shop Inventory & POS System (Laravel)

A comprehensive SaaS platform for meat shops and butcher businesses built with Laravel 10.

## 🚀 Features

### Core Features
- **Multi-tenant Architecture**: Secure data isolation for each meat shop
- **Inventory Management**: Batch-level tracking with expiry monitoring
- **Weight-based POS System**: Accurate sales processing for meat products
- **Offline Mode**: Continue sales operations without internet connectivity
- **Real-time Stock Updates**: Automatic inventory updates during sales
- **Supplier & Customer Management**: Complete relationship management
- **Advanced Reporting**: Sales, inventory, and performance analytics

### Subscription Plans
- 🟢 **Basic** - $29/month: Small shops with basic inventory needs
- 🔵 **Standard** - $79/month: Growing businesses with POS functionality
- 🟣 **Premium** - $149/month: Advanced operations with API access
- 🏢 **Enterprise** - Custom: Large-scale operations with dedicated infrastructure

## 🛠️ Technology Stack

### Backend
- **Laravel 10** with PHP 8.1+
- **MySQL** for primary database
- **Redis** for caching and sessions
- **Sanctum** for API authentication
- **Spatie Multi-tenancy** for tenant isolation

### Additional Services
- **Stripe** for payment processing
- **Twilio** for SMS notifications
- **Mailgun/SendGrid** for email notifications
- **SQLite** for offline POS operations

## 📋 User Roles

### Owner
- Full system access
- Subscription and user management
- Complete report access

### Manager
- Inventory and supplier management
- Sales oversight
- Limited administrative access

### Cashier
- POS transactions
- Payment processing
- Receipt printing

### Inventory Staff
- Stock level updates
- Delivery recording
- Batch management

## 🏗️ Project Structure

```
meatshop-pos-laravel/
├── app/
│   ├── Http/Controllers/          # API Controllers
│   ├── Models/                  # Eloquent Models
│   ├── Jobs/                    # Queue Jobs
│   ├── Notifications/           # Notification Classes
│   └── Services/                # Business Logic Services
├── database/
│   ├── migrations/              # Database Migrations
│   ├── seeders/                # Database Seeders
│   └── factories/              # Model Factories
├── routes/
│   ├── api.php                 # API Routes
│   └── web.php                 # Web Routes
├── resources/
│   ├── views/                  # Blade Templates
│   └── js/                    # Frontend Assets
├── storage/
│   ├── app/                    # Application Files
│   └── offline_data/           # Local SQLite DBs
└── config/                    # Configuration Files
```

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Redis
- Node.js & NPM (for frontend assets)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd meatshop-pos-laravel
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Link storage**
   ```bash
   php artisan storage:link
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

7. **Build frontend assets**
   ```bash
   npm run dev
   ```

### Environment Variables

Key environment variables to configure:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meatshop_pos
DB_USERNAME=root
DB_PASSWORD=your_password

# Application
APP_NAME="Meat Shop POS"
APP_URL=http://localhost

# Stripe
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_secret

# Twilio
TWILIO_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_PHONE_NUMBER=your_phone_number

# Multi-tenancy
TENANT_MODEL=App\Models\Tenant
```

## 📊 Database Schema

### Multi-tenant Design
- Each tenant has a unique `tenant_id`
- All models include tenant isolation
- Secure data separation between shops

### Key Tables
- **tenants**: Shop information and subscription details
- **users**: Authentication and role management
- **products**: Product catalog with weight-based pricing
- **inventory_batches**: Stock levels with batch tracking
- **sales**: Transaction records and receipts
- **customers**: Customer database with loyalty
- **suppliers**: Vendor management

## 🔐 Security Features

- Laravel Sanctum for API authentication
- Role-based access control (RBAC)
- API rate limiting
- Tenant data isolation
- Input validation and sanitization

## 📱 Offline Capabilities

The system supports offline POS operations:

- Local SQLite database for offline transactions
- Automatic sync when connectivity is restored
- Queue-based transaction processing
- Conflict resolution mechanisms

## 📈 Reporting & Analytics

- **Sales Reports**: Daily, weekly, monthly summaries
- **Inventory Reports**: Stock levels, expiry alerts
- **Performance Analytics**: Revenue trends, product performance
- **Custom Reports**: Exportable data (CSV, Excel, PDF)

## 🔗 API Documentation

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout

### Inventory Management
- `GET /api/products` - List products
- `POST /api/products` - Create product
- `GET /api/inventory` - List inventory
- `POST /api/inventory/batch` - Add inventory batch

### POS Operations
- `POST /api/sales` - Process sale
- `GET /api/sales` - List sales
- `POST /api/sales/{id}/void` - Void sale

## 🧪 Testing

```bash
# Run PHPUnit tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 🚀 Deployment

### Production Setup
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link
```

### Environment Setup
- Set `APP_ENV=production`
- Configure production database
- Set up SSL certificates
- Configure reverse proxy (nginx/Apache)

## 📞 Support

For support and inquiries:
- Email: support@meatshop-pos.com
- Documentation: https://docs.meatshop-pos.com
- Community Forum: https://community.meatshop-pos.com

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

---

Built with ❤️ for meat shop owners worldwide using Laravel 10
