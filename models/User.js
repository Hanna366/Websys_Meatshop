const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');

const userSchema = new mongoose.Schema({
  tenant_id: {
    type: String,
    required: true,
    index: true
  },
  username: {
    type: String,
    required: true,
    trim: true
  },
  email: {
    type: String,
    required: true,
    unique: true,
    lowercase: true,
    trim: true
  },
  password: {
    type: String,
    required: true,
    minlength: 6
  },
  role: {
    type: String,
    enum: ['owner', 'manager', 'cashier', 'inventory_staff'],
    required: true
  },
  profile: {
    first_name: String,
    last_name: String,
    phone: String,
    avatar_url: String,
    address: {
      street: String,
      city: String,
      state: String,
      zip_code: String
    }
  },
  permissions: {
    can_manage_users: { type: Boolean, default: false },
    can_manage_inventory: { type: Boolean, default: false },
    can_process_sales: { type: Boolean, default: false },
    can_view_reports: { type: Boolean, default: false },
    can_manage_suppliers: { type: Boolean, default: false },
    can_manage_customers: { type: Boolean, default: false },
    can_export_data: { type: Boolean, default: false },
    can_access_api: { type: Boolean, default: false }
  },
  preferences: {
    language: { type: String, default: 'en' },
    timezone: { type: String, default: 'UTC' },
    theme: { type: String, enum: ['light', 'dark'], default: 'light' },
    email_notifications: { type: Boolean, default: true },
    sms_notifications: { type: Boolean, default: false }
  },
  last_login: Date,
  login_attempts: {
    count: { type: Number, default: 0 },
    lock_until: Date
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

// Hash password before saving
userSchema.pre('save', async function(next) {
  if (!this.isModified('password')) return next();
  
  try {
    const salt = await bcrypt.genSalt(10);
    this.password = await bcrypt.hash(this.password, salt);
    next();
  } catch (error) {
    next(error);
  }
});

// Update updated_at field before saving
userSchema.pre('save', function(next) {
  this.updated_at = new Date();
  next();
});

// Set permissions based on role
userSchema.pre('save', function(next) {
  if (this.isModified('role')) {
    switch (this.role) {
      case 'owner':
        this.permissions = {
          can_manage_users: true,
          can_manage_inventory: true,
          can_process_sales: true,
          can_view_reports: true,
          can_manage_suppliers: true,
          can_manage_customers: true,
          can_export_data: true,
          can_access_api: true
        };
        break;
      case 'manager':
        this.permissions = {
          can_manage_users: false,
          can_manage_inventory: true,
          can_process_sales: true,
          can_view_reports: true,
          can_manage_suppliers: true,
          can_manage_customers: true,
          can_export_data: true,
          can_access_api: false
        };
        break;
      case 'cashier':
        this.permissions = {
          can_manage_users: false,
          can_manage_inventory: false,
          can_process_sales: true,
          can_view_reports: false,
          can_manage_suppliers: false,
          can_manage_customers: true,
          can_export_data: false,
          can_access_api: false
        };
        break;
      case 'inventory_staff':
        this.permissions = {
          can_manage_users: false,
          can_manage_inventory: true,
          can_process_sales: false,
          can_view_reports: false,
          can_manage_suppliers: false,
          can_manage_customers: false,
          can_export_data: false,
          can_access_api: false
        };
        break;
    }
  }
  next();
});

// Method to compare password
userSchema.methods.comparePassword = async function(candidatePassword) {
  return bcrypt.compare(candidatePassword, this.password);
};

// Method to check if account is locked
userSchema.methods.isLocked = function() {
  return !!(this.login_attempts.lock_until && this.login_attempts.lock_until > Date.now());
};

// Method to increment login attempts
userSchema.methods.incLoginAttempts = function() {
  // If we have a previous lock that has expired, restart at 1
  if (this.login_attempts.lock_until && this.login_attempts.lock_until < Date.now()) {
    return this.updateOne({
      $unset: { 'login_attempts.lock_until': 1 },
      $set: { 'login_attempts.count': 1 }
    });
  }
  
  const updates = { $inc: { 'login_attempts.count': 1 } };
  
  // Lock account after 5 failed attempts for 2 hours
  if (this.login_attempts.count + 1 >= 5 && !this.isLocked()) {
    updates.$set = { 'login_attempts.lock_until': Date.now() + 2 * 60 * 60 * 1000 }; // 2 hours
  }
  
  return this.updateOne(updates);
};

// Method to reset login attempts
userSchema.methods.resetLoginAttempts = function() {
  return this.updateOne({
    $unset: { 'login_attempts': 1 },
    $set: { last_login: Date.now() }
  });
};

// Indexes
userSchema.index({ tenant_id: 1, email: 1 }, { unique: true });
userSchema.index({ tenant_id: 1, role: 1 });
userSchema.index({ status: 1 });

module.exports = mongoose.model('User', userSchema);
