# 🥩 Meat Shop Inventory & POS System

A comprehensive SaaS platform designed for meat shops and butcher businesses to manage inventory, sales, and daily operations efficiently.

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
- **Node.js** with Express.js
- **MongoDB** with Mongoose ODM
- **JWT** for authentication
- **Stripe** for payment processing
- **Socket.io** for real-time updates

### Frontend
- **React.js** with modern hooks
- **Material-UI** for components
- **Chart.js** for analytics
- **Redux** for state management

### Additional Features
- **Offline Storage**: SQLite for local POS operations
- **Email Notifications**: Nodemailer integration
- **SMS Alerts**: Twilio integration
- **File Uploads**: Multer for product images
- **PDF Generation**: Receipt and report generation

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
meatshop-pos-system/
├── server.js                 # Main server file
├── package.json             # Dependencies and scripts
├── .env.example             # Environment variables template
├── models/                  # Database models
├── routes/                  # API routes
├── middleware/              # Custom middleware
├── controllers/             # Route controllers
├── utils/                   # Utility functions
├── client/                  # React frontend
├── uploads/                 # File uploads directory
└── offline_data/            # Local database for offline mode
```

## 🚀 Getting Started

### Prerequisites
- Node.js 14+
- MongoDB 4.4+
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd meatshop-pos-system
   ```

2. **Install dependencies**
   ```bash
   npm run setup
   ```

3. **Set up environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Start the development server**
   ```bash
   npm run dev
   ```

5. **Start the frontend** (in a separate terminal)
   ```bash
   npm run client
   ```

### Environment Variables

Create a `.env` file based on `.env.example` and configure:

- Database connection strings
- JWT secret keys
- Stripe API keys
- Email service credentials
- SMS service credentials

## 📊 Database Schema

### Multi-tenant Design
- Each tenant has a unique `tenant_id`
- All data models include tenant isolation
- Secure data separation between shops

### Key Collections
- **Tenants**: Shop information and subscription details
- **Users**: Authentication and role management
- **Products**: Product catalog with weight-based pricing
- **Inventory**: Stock levels with batch tracking
- **Sales**: Transaction records and receipts
- **Suppliers**: Vendor management
- **Customers**: Customer database

## 🔐 Security Features

- JWT-based authentication
- Role-based access control (RBAC)
- Rate limiting for API protection
- Data encryption for sensitive information
- CORS configuration
- Helmet.js for security headers

## 📱 Offline Mode

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
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/refresh` - Token refresh

### Inventory Management
- `GET /api/inventory` - List inventory items
- `POST /api/inventory` - Add inventory
- `PUT /api/inventory/:id` - Update inventory
- `DELETE /api/inventory/:id` - Remove inventory

### POS Operations
- `POST /api/sales` - Process sale
- `GET /api/sales` - List sales
- `GET /api/sales/:id` - Get sale details

## 🧪 Testing

```bash
# Run all tests
npm test

# Run with coverage
npm run test:coverage
```

## 🚀 Deployment

### Production Build
```bash
npm run build
```

### Environment Setup
- Set `NODE_ENV=production`
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

Built with ❤️ for meat shop owners worldwide
