const mongoose = require('mongoose');

const productSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    index: true
  },
  product_code: {
    type: String,
    required: true,
    trim: true
  },
  name: {
    type: String,
    required: true,
    trim: true
  },
  description: {
    type: String,
    trim: true
  },
  category: {
    type: String,
    required: true,
    enum: ['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other']
  },
  subcategory: {
    type: String,
    trim: true
  },
  pricing: {
    price_per_unit: {
      type: Number,
      required: true,
      min: 0
    },
    unit_type: {
      type: String,
      enum: ['lb', 'kg', 'piece'],
      required: true
    },
    cost_per_unit: {
      type: Number,
      min: 0
    },
    markup_percentage: {
      type: Number,
      min: 0,
      max: 100
    },
    tax_rate: {
      type: Number,
      min: 0,
      max: 100,
      default: 0
    }
  },
  inventory: {
    current_stock: {
      type: Number,
      default: 0,
      min: 0
    },
    reorder_level: {
      type: Number,
      default: 10,
      min: 0
    },
    max_stock: {
      type: Number,
      min: 0
    },
    unit_of_measure: {
      type: String,
      enum: ['lb', 'kg', 'piece'],
      required: true
    },
    weight_variance: {
      type: Number,
      default: 0.1, // 10% variance allowance
      min: 0,
      max: 1
    }
  },
  batch_tracking: {
    enabled: { type: Boolean, default: true },
    expiry_tracking: { type: Boolean, default: true },
    shelf_life_days: { type: Number, min: 0 }
  },
  physical_attributes: {
    cut_type: {
      type: String,
      enum: ['whole', 'steak', 'ground', 'roast', 'chop', 'sausage', 'other']
    },
    grade: {
      type: String,
      enum: ['prime', 'choice', 'select', 'standard', 'commercial']
    },
    processing_method: {
      type: String,
      enum: ['fresh', 'frozen', 'cured', 'smoked', 'aged']
    },
    storage_requirements: {
      temperature_min: Number,
      temperature_max: Number,
      humidity_level: Number,
      storage_type: {
        type: String,
        enum: ['refrigerated', 'frozen', 'dry', 'ambient']
      }
    }
  },
  supplier_info: {
    primary_supplier_id: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Supplier'
    },
    backup_supplier_ids: [{
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Supplier'
    }],
    supplier_sku: String
  },
  images: [{
    url: String,
    alt_text: String,
    is_primary: { type: Boolean, default: false }
  }],
  tags: [String],
  status: {
    type: String,
    enum: ['active', 'inactive', 'discontinued'],
    default: 'active'
  },
  barcode: {
    type: String,
    unique: true,
    sparse: true
  },
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
productSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Compound indexes for efficient queries
productSchema.index({ tenant_id: 1, product_code: 1 }, { unique: true });
productSchema.index({ tenant_id: 1, category: 1 });
productSchema.index({ tenant_id: 1, status: 1 });
productSchema.index({ tenant_id: 1, 'inventory.current_stock': 1 });
productSchema.index({ tenant_id: 1, barcode: 1 }, { sparse: true });

// Virtual for profit margin
productSchema.virtual('profit_margin').get(function() {
  if (!this.pricing.cost_per_unit || !this.pricing.price_per_unit) return 0;
  return ((this.pricing.price_per_unit - this.pricing.cost_per_unit) / this.pricing.price_per_unit) * 100;
});

// Method to check if product is low on stock
productSchema.methods.isLowStock = function() {
  return this.inventory.current_stock <= this.inventory.reorder_level;
};

// Method to get effective price with tax
productSchema.methods.getEffectivePrice = function() {
  const basePrice = this.pricing.price_per_unit;
  const taxAmount = basePrice * (this.pricing.tax_rate / 100);
  return basePrice + taxAmount;
};

module.exports = mongoose.model('Product', productSchema);
