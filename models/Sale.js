const mongoose = require('mongoose');

const saleItemSchema = new mongoose.Schema({
  product_id: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Product',
    required: true
  },
  batch_id: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'InventoryBatch',
    required: true
  },
  product_name: {
    type: String,
    required: true
  },
  product_code: {
    type: String,
    required: true
  },
  quantity: {
    weight: {
      type: Number,
      required: true,
      min: 0
    },
    unit: {
      type: String,
      enum: ['lb', 'kg', 'piece'],
      required: true
    },
    pieces: {
      type: Number,
      min: 0
    }
  },
  pricing: {
    unit_price: {
      type: Number,
      required: true,
      min: 0
    },
    total_price: {
      type: Number,
      required: true,
      min: 0
    },
    discount_amount: {
      type: Number,
      default: 0,
      min: 0
    },
    discount_type: {
      type: String,
      enum: ['percentage', 'fixed'],
      default: 'fixed'
    },
    tax_rate: {
      type: Number,
      default: 0,
      min: 0
    },
    tax_amount: {
      type: Number,
      default: 0,
      min: 0
    }
  },
  quality_notes: String,
  special_instructions: String
});

const saleSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    index: true
  },
  sale_number: {
    type: String,
    required: true,
    unique: true
  },
  customer_id: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Customer'
  },
  customer_info: {
    name: String,
    phone: String,
    email: String,
    address: String
  },
  items: [saleItemSchema],
  payment: {
    subtotal: {
      type: Number,
      required: true,
      min: 0
    },
    discount_amount: {
      type: Number,
      default: 0,
      min: 0
    },
    tax_amount: {
      type: Number,
      default: 0,
      min: 0
    },
    total_amount: {
      type: Number,
      required: true,
      min: 0
    },
    amount_paid: {
      type: Number,
      required: true,
      min: 0
    },
    change_due: {
      type: Number,
      default: 0
    },
    payment_method: {
      type: String,
      enum: ['cash', 'card', 'check', 'mobile_pay', 'store_credit'],
      required: true
    },
    payment_status: {
      type: String,
      enum: ['paid', 'partial', 'pending', 'refunded'],
      default: 'paid'
    },
    card_details: {
      last_four: String,
      card_type: String,
      transaction_id: String,
      authorization_code: String
    }
  },
  staff: {
    cashier_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User',
      required: true
    },
    cashier_name: {
      type: String,
      required: true
    }
  },
  transaction: {
    date: {
      type: Date,
      required: true,
      default: Date.now
    },
    register_id: String,
    terminal_id: String,
    shift_id: String,
    is_offline: {
      type: Boolean,
      default: false
    },
    synced_at: Date,
    voided: {
      is_voided: { type: Boolean, default: false },
      voided_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
      voided_at: Date,
      void_reason: String
    }
  },
  receipt: {
    printed: { type: Boolean, default: false },
    emailed: { type: Boolean, default: false },
    sms_sent: { type: Boolean, default: false },
    receipt_url: String
  },
  notes: {
    customer_notes: String,
    internal_notes: String
  },
  loyalty: {
    points_earned: { type: Number, default: 0 },
    points_redeemed: { type: Number, default: 0 },
    loyalty_member_id: String
  },
  status: {
    type: String,
    enum: ['completed', 'pending', 'cancelled', 'refunded'],
    default: 'completed'
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
saleSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Calculate totals before saving
saleSchema.pre('save', function(next) {
  if (this.isModified('items')) {
    let subtotal = 0;
    let totalTax = 0;
    
    this.items.forEach(item => {
      const itemTotal = item.pricing.total_price;
      subtotal += itemTotal;
      totalTax += item.pricing.tax_amount;
    });
    
    this.payment.subtotal = subtotal;
    this.payment.tax_amount = totalTax;
    this.payment.total_amount = subtotal + this.payment.tax_amount - this.payment.discount_amount;
    
    if (this.payment.amount_paid >= this.payment.total_amount) {
      this.payment.change_due = this.payment.amount_paid - this.payment.total_amount;
    }
  }
  next();
});

// Generate unique sale number
saleSchema.pre('save', async function(next) {
  if (!this.sale_number) {
    const count = await this.constructor.countDocuments({ tenant_id: this.tenant_id });
    this.sale_number = `SALE-${this.tenant_id.slice(-4)}-${String(count + 1).padStart(6, '0')}`;
  }
  next();
});

// Compound indexes
saleSchema.index({ tenant_id: 1, 'transaction.date': -1 });
saleSchema.index({ tenant_id: 1, sale_number: 1 }, { unique: true });
saleSchema.index({ tenant_id: 1, customer_id: 1 });
saleSchema.index({ tenant_id: 1, 'staff.cashier_id': 1 });
saleSchema.index({ tenant_id: 1, status: 1 });
saleSchema.index({ tenant_id: 1, 'transaction.is_offline': 1 });

// Virtual for profit calculation
saleSchema.virtual('totalProfit').get(function() {
  let profit = 0;
  this.items.forEach(item => {
    // This would need to be calculated based on cost data
    // For now, we'll use a placeholder
    profit += item.pricing.total_price * 0.3; // Assuming 30% margin
  });
  return profit;
});

// Method to void sale
saleSchema.methods.voidSale = function(reason, voidedBy) {
  this.transaction.voided.is_voided = true;
  this.transaction.voided.voided_by = voidedBy;
  this.transaction.voided.voided_at = new Date();
  this.transaction.voided.void_reason = reason;
  this.status = 'cancelled';
};

// Method to check if sale can be refunded
saleSchema.methods.canBeRefunded = function() {
  return this.status === 'completed' && 
         !this.transaction.voided.is_voided &&
         (new Date() - this.transaction.date) <= (30 * 24 * 60 * 60 * 1000); // Within 30 days
};

module.exports = mongoose.model('Sale', saleSchema);
