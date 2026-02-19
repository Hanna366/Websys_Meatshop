const mongoose = require('mongoose');

const inventoryBatchSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    index: true
  },
  product_id: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Product',
    required: true,
    index: true
  },
  batch_number: {
    type: String,
    required: true,
    trim: true
  },
  supplier_id: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Supplier',
    required: true
  },
  quantity: {
    initial_quantity: {
      type: Number,
      required: true,
      min: 0
    },
    current_quantity: {
      type: Number,
      required: true,
      min: 0
    },
    unit: {
      type: String,
      enum: ['lb', 'kg', 'piece'],
      required: true
    }
  },
  cost: {
    unit_cost: {
      type: Number,
      required: true,
      min: 0
    },
    total_cost: {
      type: Number,
      required: true,
      min: 0
    },
    currency: {
      type: String,
      default: 'USD'
    }
  },
  dates: {
    received_date: {
      type: Date,
      required: true
    },
    production_date: Date,
    expiry_date: {
      type: Date,
      required: true
    },
    freeze_date: Date,
    thaw_date: Date
  },
  quality: {
    grade: {
      type: String,
      enum: ['prime', 'choice', 'select', 'standard', 'commercial']
    },
    inspection_passed: {
      type: Boolean,
      default: true
    },
    inspection_notes: String,
    temperature_on_receipt: Number,
    condition_on_receipt: {
      type: String,
      enum: ['excellent', 'good', 'fair', 'poor']
    }
  },
  storage: {
    location: String,
    temperature_zone: {
      type: String,
      enum: ['freezer', 'refrigerator', 'dry_storage']
    },
    shelf_position: String
  },
  tracking: {
    barcode: String,
    qr_code: String,
    rfid_tag: String
  },
  status: {
    type: String,
    enum: ['active', 'expiring_soon', 'expired', 'depleted', 'quarantined', 'recalled'],
    default: 'active'
  },
  alerts: {
    low_stock_alert_sent: { type: Boolean, default: false },
    expiry_alert_sent: { type: Boolean, default: false },
    quality_issue_alert_sent: { type: Boolean, default: false }
  },
  waste_tracking: {
    waste_quantity: { type: Number, default: 0 },
    waste_reason: {
      type: String,
      enum: ['expiry', 'spoilage', 'damage', 'contamination', 'theft', 'other']
    },
    waste_recorded_by: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User'
    },
    waste_notes: String
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
inventoryBatchSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Check and update batch status based on expiry date
inventoryBatchSchema.pre('save', function(next) {
  const now = new Date();
  const expiryDate = new Date(this.dates.expiry_date);
  const daysToExpiry = Math.ceil((expiryDate - now) / (1000 * 60 * 60 * 24));
  
  if (this.quantity.current_quantity === 0) {
    this.status = 'depleted';
  } else if (expiryDate < now) {
    this.status = 'expired';
  } else if (daysToExpiry <= 3) {
    this.status = 'expiring_soon';
  } else {
    this.status = 'active';
  }
  
  next();
});

// Compound indexes
inventoryBatchSchema.index({ tenant_id: 1, product_id: 1 });
inventoryBatchSchema.index({ tenant_id: 1, batch_number: 1 }, { unique: true });
inventoryBatchSchema.index({ tenant_id: 1, status: 1 });
inventoryBatchSchema.index({ tenant_id: 1, 'dates.expiry_date': 1 });
inventoryBatchSchema.index({ tenant_id: 1, supplier_id: 1 });

// Virtual for days until expiry
inventoryBatchSchema.virtual('daysUntilExpiry').get(function() {
  const now = new Date();
  const expiryDate = new Date(this.dates.expiry_date);
  return Math.ceil((expiryDate - now) / (1000 * 60 * 60 * 24));
});

// Virtual for percentage remaining
inventoryBatchSchema.virtual('percentageRemaining').get(function() {
  if (this.quantity.initial_quantity === 0) return 0;
  return (this.quantity.current_quantity / this.quantity.initial_quantity) * 100;
});

// Method to check if batch is expiring soon
inventoryBatchSchema.methods.isExpiringSoon = function(days = 7) {
  return this.daysUntilExpiry <= days && this.daysUntilExpiry > 0;
};

// Method to check if batch is expired
inventoryBatchSchema.methods.isExpired = function() {
  return this.daysUntilExpiry <= 0;
};

// Method to record waste
inventoryBatchSchema.methods.recordWaste = function(quantity, reason, recordedBy, notes) {
  this.waste_tracking.waste_quantity += quantity;
  this.waste_tracking.waste_reason = reason;
  this.waste_tracking.waste_recorded_by = recordedBy;
  this.waste_tracking.waste_notes = notes;
  this.quantity.current_quantity -= quantity;
  
  if (this.quantity.current_quantity < 0) {
    this.quantity.current_quantity = 0;
  }
};

module.exports = mongoose.model('InventoryBatch', inventoryBatchSchema);
