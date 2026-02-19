const mongoose = require('mongoose');

const customerSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    index: true
  },
  customer_code: {
    type: String,
    required: true,
    trim: true
  },
  personal_info: {
    first_name: {
      type: String,
      required: true,
      trim: true
    },
    last_name: {
      type: String,
      required: true,
      trim: true
    },
    phone: {
      type: String,
      required: true
    },
    email: {
      type: String,
      lowercase: true,
      trim: true
    },
    date_of_birth: Date,
    gender: {
      type: String,
      enum: ['male', 'female', 'other', 'prefer_not_to_say']
    }
  },
  address: {
    street: String,
    city: String,
    state: String,
    zip_code: String,
    country: { type: String, default: 'US' },
    is_same_as_billing: { type: Boolean, default: true }
  },
  billing_address: {
    street: String,
    city: String,
    state: String,
    zip_code: String,
    country: { type: String, default: 'US' }
  },
  preferences: {
    preferred_contact_method: {
      type: String,
      enum: ['phone', 'email', 'sms', 'mail'],
      default: 'phone'
    },
    marketing_consent: {
      email: { type: Boolean, default: false },
      sms: { type: Boolean, default: false },
      phone: { type: Boolean, default: false }
    },
    notification_preferences: {
      order_updates: { type: Boolean, default: true },
      promotions: { type: Boolean, default: false },
      new_products: { type: Boolean, default: false },
      expiry_alerts: { type: Boolean, default: true }
    }
  },
  loyalty: {
    member_id: String,
    is_member: { type: Boolean, default: false },
    join_date: Date,
    points_balance: { type: Number, default: 0 },
    points_earned: { type: Number, default: 0 },
    points_redeemed: { type: Number, default: 0 },
    tier: {
      type: String,
      enum: ['bronze', 'silver', 'gold', 'platinum'],
      default: 'bronze'
    },
    rewards_earned: [{
      reward_type: String,
      reward_value: Number,
      earned_date: { type: Date, default: Date.now },
      expiry_date: Date,
      is_used: { type: Boolean, default: false }
    }]
  },
  purchasing_history: {
    total_orders: { type: Number, default: 0 },
    total_spent: { type: Number, default: 0 },
    average_order_value: { type: Number, default: 0 },
    first_purchase_date: Date,
    last_purchase_date: Date,
    favorite_products: [{
      product_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Product' },
      purchase_count: { type: Number, default: 0 },
      total_spent: { type: Number, default: 0 }
    }],
    purchase_frequency: {
      type: String,
      enum: ['daily', 'weekly', 'bi_weekly', 'monthly', 'quarterly', 'occasional'],
      default: 'occasional'
    }
  },
  payment_methods: [{
    type: {
      type: String,
      enum: ['credit_card', 'debit_card', 'cash', 'check', 'store_credit'],
      required: true
    },
    is_default: { type: Boolean, default: false },
    card_details: {
      last_four: String,
      card_type: String,
      expiry_month: Number,
      expiry_year: Number,
      token: String
    },
    is_active: { type: Boolean, default: true }
  }],
  special_requirements: {
    dietary_restrictions: [String],
    allergies: [String],
    preferences: [String],
    special_instructions: String
  },
  business_info: {
    is_business_customer: { type: Boolean, default: false },
    business_name: String,
    business_type: {
      type: String,
      enum: ['restaurant', 'retail', 'catering', 'institutional', 'other']
    },
    tax_id: String,
    account_manager: String,
    credit_limit: Number,
    payment_terms: String
  },
  communication: {
    last_contact_date: Date,
    contact_history: [{
      date: { type: Date, default: Date.now },
      type: {
        type: String,
        enum: ['phone_call', 'email', 'sms', 'in_person', 'other']
      },
      purpose: String,
      notes: String,
      staff_member: { type: mongoose.Schema.Types.ObjectId, ref: 'User' }
    }]
  },
  status: {
    type: String,
    enum: ['active', 'inactive', 'suspended', 'blacklisted'],
    default: 'active'
  },
  tags: [String],
  notes: String,
  created_by: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  updated_by: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User'
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
customerSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Update purchasing history metrics
customerSchema.pre('save', function(next) {
  if (this.isModified('purchasing_history.total_orders') || this.isModified('purchasing_history.total_spent')) {
    if (this.purchasing_history.total_orders > 0) {
      this.purchasing_history.average_order_value = 
        this.purchasing_history.total_spent / this.purchasing_history.total_orders;
    }
  }
  next();
});

// Update loyalty tier based on points
customerSchema.pre('save', function(next) {
  if (this.isModified('loyalty.points_balance')) {
    const points = this.loyalty.points_balance;
    if (points >= 10000) {
      this.loyalty.tier = 'platinum';
    } else if (points >= 5000) {
      this.loyalty.tier = 'gold';
    } else if (points >= 2000) {
      this.loyalty.tier = 'silver';
    } else {
      this.loyalty.tier = 'bronze';
    }
  }
  next();
});

// Compound indexes
customerSchema.index({ tenant_id: 1, customer_code: 1 }, { unique: true });
customerSchema.index({ tenant_id: 1, 'personal_info.email': 1 }, { sparse: true });
customerSchema.index({ tenant_id: 1, 'personal_info.phone': 1 });
customerSchema.index({ tenant_id: 1, status: 1 });
customerSchema.index({ tenant_id: 1, 'loyalty.is_member': 1 });

// Virtual for full name
customerSchema.virtual('full_name').get(function() {
  return `${this.personal_info.first_name} ${this.personal_info.last_name}`;
});

// Virtual for customer lifetime value
customerSchema.virtual('lifetime_value').get(function() {
  return this.purchasing_history.total_spent;
});

// Method to add loyalty points
customerSchema.methods.addLoyaltyPoints = function(points, reason) {
  this.loyalty.points_balance += points;
  this.loyalty.points_earned += points;
  
  // Add to rewards history if applicable
  if (points > 0) {
    this.loyalty.rewards_earned.push({
      reward_type: 'points',
      reward_value: points,
      earned_date: new Date()
    });
  }
};

// Method to redeem loyalty points
customerSchema.methods.redeemLoyaltyPoints = function(points, reason) {
  if (this.loyalty.points_balance >= points) {
    this.loyalty.points_balance -= points;
    this.loyalty.points_redeemed += points;
    return true;
  }
  return false;
};

// Method to update purchase history
customerSchema.methods.updatePurchaseHistory = function(orderAmount, productId = null) {
  this.purchasing_history.total_orders += 1;
  this.purchasing_history.total_spent += orderAmount;
  
  if (!this.purchasing_history.first_purchase_date) {
    this.purchasing_history.first_purchase_date = new Date();
  }
  
  this.purchasing_history.last_purchase_date = new Date();
  
  // Update favorite products
  if (productId) {
    const existingProduct = this.purchasing_history.favorite_products.find(
      p => p.product_id.toString() === productId.toString()
    );
    
    if (existingProduct) {
      existingProduct.purchase_count += 1;
      existingProduct.total_spent += orderAmount;
    } else {
      this.purchasing_history.favorite_products.push({
        product_id: productId,
        purchase_count: 1,
        total_spent: orderAmount
      });
    }
  }
};

module.exports = mongoose.model('Customer', customerSchema);
