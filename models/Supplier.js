const mongoose = require('mongoose');

const supplierSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    index: true
  },
  supplier_code: {
    type: String,
    required: true,
    trim: true
  },
  business_name: {
    type: String,
    required: true,
    trim: true
  },
  contact_info: {
    primary_contact: {
      name: { type: String, required: true },
      title: String,
      phone: { type: String, required: true },
      email: { type: String, required: true, lowercase: true }
    },
    secondary_contact: {
      name: String,
      title: String,
      phone: String,
      email: { type: String, lowercase: true }
    },
    billing_contact: {
      name: String,
      phone: String,
      email: { type: String, lowercase: true }
    }
  },
  address: {
    street: { type: String, required: true },
    city: { type: String, required: true },
    state: { type: String, required: true },
    zip_code: { type: String, required: true },
    country: { type: String, default: 'US' }
  },
  business_details: {
    tax_id: String,
    business_license: String,
    certifications: [{
      name: String,
      number: String,
      expiry_date: Date,
      issuing_authority: String
    }],
    years_in_business: Number,
    website: String
  },
  products: [{
    product_category: {
      type: String,
      enum: ['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other']
    },
    product_types: [String],
    quality_grades: [String],
    packaging_options: [String],
    lead_time_days: Number,
    minimum_order_quantity: Number
  }],
  payment_terms: {
    payment_method: {
      type: String,
      enum: ['cash', 'check', 'wire', 'credit_card', 'net_30', 'net_60'],
      required: true
    },
    credit_limit: Number,
    due_days: Number,
    early_payment_discount: {
      percentage: Number,
      days: Number
    }
  },
  delivery: {
    delivery_schedule: [String], // e.g., ['Monday', 'Wednesday', 'Friday']
    delivery_window: {
      start_time: String,
      end_time: String
    },
    delivery_fee: Number,
    minimum_order_for_free_delivery: Number,
    delivery_instructions: String
  },
  quality_standards: {
    quality_score: {
      type: Number,
      min: 0,
      max: 100,
      default: 0
    },
    last_inspection_date: Date,
    inspection_frequency: String,
    quality_metrics: {
      on_time_delivery_rate: Number,
      order_accuracy_rate: Number,
      product_quality_score: Number,
      packaging_quality_score: Number
    }
  },
  performance: {
    total_orders: { type: Number, default: 0 },
    total_value: { type: Number, default: 0 },
    average_order_value: { type: Number, default: 0 },
    last_order_date: Date,
    days_since_last_order: Number,
    return_rate: { type: Number, default: 0 },
    complaint_rate: { type: Number, default: 0 }
  },
  status: {
    type: String,
    enum: ['active', 'inactive', 'suspended', 'under_review'],
    default: 'active'
  },
  preferences: {
    preferred_supplier: { type: Boolean, default: false },
    backup_supplier: { type: Boolean, default: false },
    exclusive_supplier: { type: Boolean, default: false }
  },
  notes: {
    general_notes: String,
    special_instructions: String,
    pricing_notes: String,
    quality_notes: String
  },
  documents: [{
    name: String,
    type: {
      type: String,
      enum: ['contract', 'certificate', 'insurance', 'w9', 'other']
    },
    url: String,
    upload_date: { type: Date, default: Date.now },
    expiry_date: Date
  }],
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
supplierSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Update performance metrics
supplierSchema.pre('save', function(next) {
  if (this.isModified('performance.total_orders') || this.isModified('performance.total_value')) {
    if (this.performance.total_orders > 0) {
      this.performance.average_order_value = this.performance.total_value / this.performance.total_orders;
    }
    
    if (this.performance.last_order_date) {
      const now = new Date();
      const lastOrder = new Date(this.performance.last_order_date);
      this.performance.days_since_last_order = Math.floor((now - lastOrder) / (1000 * 60 * 60 * 24));
    }
  }
  next();
});

// Compound indexes
supplierSchema.index({ tenant_id: 1, supplier_code: 1 }, { unique: true });
supplierSchema.index({ tenant_id: 1, status: 1 });
supplierSchema.index({ tenant_id: 1, 'preferences.preferred_supplier': 1 });
supplierSchema.index({ tenant_id: 1, 'quality_standards.quality_score': -1 });

// Virtual for supplier rating
supplierSchema.virtual('overallRating').get(function() {
  const qualityScore = this.quality_standards.quality_score || 0;
  const onTimeRate = this.quality_standards.quality_metrics.on_time_delivery_rate || 0;
  const accuracyRate = this.quality_standards.quality_metrics.order_accuracy_rate || 0;
  
  return (qualityScore * 0.5) + (onTimeRate * 0.25) + (accuracyRate * 0.25);
});

// Method to check if supplier has expiring documents
supplierSchema.methods.hasExpiringDocuments = function(days = 30) {
  const now = new Date();
  const threshold = new Date(now.getTime() + days * 24 * 60 * 60 * 1000);
  
  return this.documents.some(doc => 
    doc.expiry_date && doc.expiry_date <= threshold
  );
};

// Method to update quality score
supplierSchema.methods.updateQualityScore = function(newScore) {
  // Weighted average calculation
  const oldScore = this.quality_standards.quality_score || 0;
  const totalOrders = this.performance.total_orders || 1;
  
  // Simple moving average with more weight on recent scores
  const weight = Math.min(0.3, 10 / totalOrders); // Max 30% weight for new score
  this.quality_standards.quality_score = (oldScore * (1 - weight)) + (newScore * weight);
};

module.exports = mongoose.model('Supplier', supplierSchema);
