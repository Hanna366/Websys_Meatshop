const jwt = require('jsonwebtoken');
const User = require('../models/User');
const Tenant = require('../models/Tenant');

// Middleware to authenticate JWT token
const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      return res.status(401).json({ 
        success: false, 
        message: 'Access token required' 
      });
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Get user with tenant information
    const user = await User.findOne({ 
      email: decoded.email,
      tenant_id: decoded.tenant_id 
    }).populate('tenant_id');

    if (!user) {
      return res.status(401).json({ 
        success: false, 
        message: 'Invalid token - user not found' 
      });
    }

    if (user.status !== 'active') {
      return res.status(401).json({ 
        success: false, 
        message: 'Account is not active' 
      });
    }

    // Check if user is locked
    if (user.isLocked()) {
      return res.status(423).json({ 
        success: false, 
        message: 'Account is locked due to too many failed login attempts' 
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

    req.user = user;
    req.tenant = tenant;
    next();
  } catch (error) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({ 
        success: false, 
        message: 'Invalid token' 
      });
    }
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({ 
        success: false, 
        message: 'Token expired' 
      });
    }
    return res.status(500).json({ 
      success: false, 
      message: 'Authentication error' 
    });
  }
};

// Middleware to check user permissions
const requirePermission = (permission) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ 
        success: false, 
        message: 'Authentication required' 
      });
    }

    if (!req.user.permissions[permission]) {
      return res.status(403).json({ 
        success: false, 
        message: `Permission denied: ${permission} required` 
      });
    }

    next();
  };
};

// Middleware to check user role
const requireRole = (roles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ 
        success: false, 
        message: 'Authentication required' 
      });
    }

    const allowedRoles = Array.isArray(roles) ? roles : [roles];
    if (!allowedRoles.includes(req.user.role)) {
      return res.status(403).json({ 
        success: false, 
        message: `Access denied. Required role: ${allowedRoles.join(' or ')}` 
      });
    }

    next();
  };
};

// Middleware to check subscription plan features
const requireFeature = (feature) => {
  return (req, res, next) => {
    if (!req.tenant) {
      return res.status(401).json({ 
        success: false, 
        message: 'Tenant information required' 
      });
    }

    const planFeatures = {
      basic: ['inventory_tracking'],
      standard: ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'basic_reporting'],
      premium: ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'advanced_reporting', 'api_access', 'batch_operations', 'data_export'],
      enterprise: ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'advanced_reporting', 'api_access', 'batch_operations', 'data_export', 'custom_integrations']
    };

    const allowedPlans = Object.keys(planFeatures).filter(plan => 
      planFeatures[plan].includes(feature)
    );

    if (!allowedPlans.includes(req.tenant.subscription.plan)) {
      return res.status(403).json({ 
        success: false, 
        message: `Feature "${feature}" requires ${allowedPlans.join(' or ')} plan` 
      });
    }

    next();
  };
};

// Middleware to check usage limits
const checkUsageLimit = (limitType) => {
  return async (req, res, next) => {
    if (!req.tenant) {
      return res.status(401).json({ 
        success: false, 
        message: 'Tenant information required' 
      });
    }

    const limits = {
      users: req.tenant.limits.max_users,
      products: req.tenant.limits.max_products,
      storage: req.tenant.limits.max_storage_mb,
      api_calls: req.tenant.limits.max_api_calls_per_month
    };

    const usage = req.tenant.usage;

    switch (limitType) {
      case 'users':
        if (usage.users_count >= limits.users) {
          return res.status(429).json({ 
            success: false, 
            message: `User limit reached (${limits.users})` 
          });
        }
        break;
      case 'products':
        if (usage.products_count >= limits.products) {
          return res.status(429).json({ 
            success: false, 
            message: `Product limit reached (${limits.products})` 
          });
        }
        break;
      case 'storage':
        if (usage.storage_used >= limits.storage) {
          return res.status(429).json({ 
            success: false, 
            message: `Storage limit reached (${limits.storage}MB)` 
          });
        }
        break;
      case 'api_calls':
        if (usage.api_calls_this_month >= limits.api_calls) {
          return res.status(429).json({ 
            success: false, 
            message: `API call limit reached (${limits.api_calls})` 
          });
        }
        break;
    }

    next();
  };
};

// Middleware to validate tenant access
const validateTenantAccess = async (req, res, next) => {
  try {
    const { tenant_id } = req.params;
    
    if (!tenant_id) {
      return res.status(400).json({ 
        success: false, 
        message: 'Tenant ID required' 
      });
    }

    if (req.user && req.user.tenant_id !== tenant_id) {
      return res.status(403).json({ 
        success: false, 
        message: 'Access denied: Cannot access other tenant data' 
      });
    }

    next();
  } catch (error) {
    return res.status(500).json({ 
      success: false, 
      message: 'Tenant validation error' 
    });
  }
};

module.exports = {
  authenticateToken,
  requirePermission,
  requireRole,
  requireFeature,
  checkUsageLimit,
  validateTenantAccess
};
