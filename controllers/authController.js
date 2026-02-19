const jwt = require('jsonwebtoken');
const User = require('../models/User');
const Tenant = require('../models/Tenant');
const { validationResult } = require('express-validator');

// Generate JWT token
const generateToken = (user) => {
  return jwt.sign(
    { 
      email: user.email, 
      tenant_id: user.tenant_id,
      user_id: user._id,
      role: user.role
    },
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRE || '7d' }
  );
};

// User registration
const register = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { 
      business_name, 
      business_email, 
      business_phone, 
      business_address,
      first_name, 
      last_name, 
      email, 
      password, 
      phone 
    } = req.body;

    // Check if tenant already exists
    const existingTenant = await Tenant.findOne({ business_email });
    if (existingTenant) {
      return res.status(400).json({
        success: false,
        message: 'Business email already registered'
      });
    }

    // Check if user already exists
    const existingUser = await User.findOne({ email });
    if (existingUser) {
      return res.status(400).json({
        success: false,
        message: 'Email already registered'
      });
    }

    // Generate unique tenant ID
    const tenant_id = `TEN${Date.now().toString(36).toUpperCase()}`;

    // Create new tenant
    const tenant = new Tenant({
      tenant_id,
      business_name,
      business_email,
      business_phone,
      business_address,
      subscription: {
        plan: 'basic',
        status: 'trial',
        start_date: new Date(),
        end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000), // 30 days trial
        monthly_price: 29,
        features: [
          { name: 'inventory_tracking', enabled: true }
        ]
      },
      limits: {
        max_users: 1,
        max_products: 100,
        max_storage_mb: 1000,
        max_api_calls_per_month: 1000
      }
    });

    await tenant.save();

    // Create owner user
    const user = new User({
      tenant_id,
      username: email,
      email,
      password,
      role: 'owner',
      profile: {
        first_name,
        last_name,
        phone
      }
    });

    await user.save();

    // Update tenant usage
    tenant.usage.users_count = 1;
    await tenant.save();

    // Generate token
    const token = generateToken(user);

    res.status(201).json({
      success: true,
      message: 'Registration successful',
      data: {
        token,
        user: {
          id: user._id,
          email: user.email,
          role: user.role,
          profile: user.profile
        },
        tenant: {
          tenant_id: tenant.tenant_id,
          business_name: tenant.business_name,
          subscription: tenant.subscription
        }
      }
    });
  } catch (error) {
    console.error('Registration error:', error);
    res.status(500).json({
      success: false,
      message: 'Registration failed',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// User login
const login = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { email, password } = req.body;

    // Find user
    const user = await User.findOne({ email });
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password'
      });
    }

    // Check if account is locked
    if (user.isLocked()) {
      return res.status(423).json({
        success: false,
        message: 'Account is locked due to too many failed login attempts'
      });
    }

    // Check if account is active
    if (user.status !== 'active') {
      return res.status(401).json({
        success: false,
        message: 'Account is not active'
      });
    }

    // Verify password
    const isPasswordValid = await user.comparePassword(password);
    if (!isPasswordValid) {
      await user.incLoginAttempts();
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password'
      });
    }

    // Get tenant information
    const tenant = await Tenant.findOne({ tenant_id: user.tenant_id });
    if (!tenant) {
      return res.status(401).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    // Check tenant status
    if (tenant.status !== 'active') {
      return res.status(401).json({
        success: false,
        message: 'Tenant account is not active'
      });
    }

    // Check subscription status
    if (tenant.subscription.status === 'suspended' || tenant.subscription.status === 'cancelled') {
      return res.status(403).json({
        success: false,
        message: 'Subscription is not active'
      });
    }

    // Reset login attempts
    await user.resetLoginAttempts();

    // Generate token
    const token = generateToken(user);

    res.json({
      success: true,
      message: 'Login successful',
      data: {
        token,
        user: {
          id: user._id,
          email: user.email,
          role: user.role,
          profile: user.profile,
          permissions: user.permissions
        },
        tenant: {
          tenant_id: tenant.tenant_id,
          business_name: tenant.business_name,
          subscription: tenant.subscription,
          settings: tenant.settings
        }
      }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({
      success: false,
      message: 'Login failed',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Refresh token
const refreshToken = async (req, res) => {
  try {
    const user = req.user;

    // Generate new token
    const token = generateToken(user);

    res.json({
      success: true,
      message: 'Token refreshed successfully',
      data: { token }
    });
  } catch (error) {
    console.error('Token refresh error:', error);
    res.status(500).json({
      success: false,
      message: 'Token refresh failed',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Logout (client-side token removal)
const logout = async (req, res) => {
  try {
    // In a stateless JWT system, logout is handled client-side
    // However, we can implement token blacklisting if needed
    res.json({
      success: true,
      message: 'Logout successful'
    });
  } catch (error) {
    console.error('Logout error:', error);
    res.status(500).json({
      success: false,
      message: 'Logout failed',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get current user profile
const getProfile = async (req, res) => {
  try {
    const user = await User.findById(req.user._id)
      .select('-password')
      .populate('tenant_id', 'business_name subscription settings');

    res.json({
      success: true,
      data: { user }
    });
  } catch (error) {
    console.error('Get profile error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get profile',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update user profile
const updateProfile = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { profile, preferences } = req.body;
    const user = await User.findById(req.user._id);

    if (profile) {
      user.profile = { ...user.profile, ...profile };
    }

    if (preferences) {
      user.preferences = { ...user.preferences, ...preferences };
    }

    user.updated_by = req.user._id;
    await user.save();

    res.json({
      success: true,
      message: 'Profile updated successfully',
      data: { user }
    });
  } catch (error) {
    console.error('Update profile error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update profile',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Change password
const changePassword = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { current_password, new_password } = req.body;
    const user = await User.findById(req.user._id);

    // Verify current password
    const isCurrentPasswordValid = await user.comparePassword(current_password);
    if (!isCurrentPasswordValid) {
      return res.status(400).json({
        success: false,
        message: 'Current password is incorrect'
      });
    }

    // Update password
    user.password = new_password;
    user.updated_by = req.user._id;
    await user.save();

    res.json({
      success: true,
      message: 'Password changed successfully'
    });
  } catch (error) {
    console.error('Change password error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to change password',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  register,
  login,
  refreshToken,
  logout,
  getProfile,
  updateProfile,
  changePassword
};
