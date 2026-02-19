const mongoose = require('mongoose');

const tenantSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    unique: true,
    index: true
  },
  business_name: {
    type: String,
    required: true,
    trim: true
  },
  business_email: {
    type: String,
    required: true,
    unique: true,
    lowercase: true
  },
  business_phone: {
    type: String,
    required: true
  },
  business_address: {
    street: String,
    city: String,
    state: String,
    zip_code: String,
    country: { type: String, default: 'US' }
  },
  subscription: {
    plan: {
      type: String,
      enum: ['basic', 'standard', 'premium', 'enterprise'],
      default: 'basic'
    },
    status: {
      type: String,
      enum: ['active', 'trial', 'suspended', 'cancelled'],
      default: 'trial'
    },
    start_date: Date,
    end_date: Date,
    stripe_customer_id: String,
    stripe_subscription_id: String,
    monthly_price: Number,
    features: [{
      name: String,
      enabled: Boolean
    }]
  },
  settings: {
    currency: { type: String, default: 'USD' },
    weight_unit: { type: String, enum: ['kg', 'lb'], default: 'lb' },
    tax_rate: { type: Number, default: 0 },
    low_stock_threshold: { type: Number, default: 10 },
    expiry_warning_days: { type: Number, default: 7 },
    enable_sms_notifications: { type: Boolean, default: false },
    enable_email_notifications: { type: Boolean, default: true },
    custom_branding: {
      logo_url: String,
      primary_color: String,
      secondary_color: String
    }
  },
  usage: {
    users_count: { type: Number, default: 0 },
    products_count: { type: Number, default: 0 },
    storage_used: { type: Number, default: 0 }, // in MB
    api_calls_this_month: { type: Number, default: 0 }
  },
  limits: {
    max_users: { type: Number, default: 1 },
    max_products: { type: Number, default: 100 },
    max_storage_mb: { type: Number, default: 1000 },
    max_api_calls_per_month: { type: Number, default: 1000 }
  },
  status: {
    type: String,
    enum: ['active', 'inactive', 'suspended'],
    default: 'active'
  },
  created_at: {
    type: Date,
    default: Date.now
  },
  updated_at: {
    type: Date,
    default: Date.now
  }
});

// Update the updated_at field before saving
tenantSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Index for efficient queries
tenantSchema.index({ 'subscription.status': 1 });
tenantSchema.index({ status: 1 });

module.exports = mongoose.model('Tenant', tenantSchema);
