const Tenant = require('../models/Tenant');
const User = require('../models/User');

// Generate unique tenant ID
const generateTenantId = async () => {
  let tenant_id;
  let isUnique = false;
  let attempts = 0;
  
  while (!isUnique && attempts < 10) {
    tenant_id = `TEN${Date.now().toString(36).toUpperCase()}${Math.random().toString(36).substr(2, 3).toUpperCase()}`;
    const existing = await Tenant.findOne({ tenant_id });
    if (!existing) {
      isUnique = true;
    }
    attempts++;
  }
  
  if (!isUnique) {
    throw new Error('Failed to generate unique tenant ID');
  }
  
  return tenant_id;
};

// Check if tenant has access to a feature
const hasFeatureAccess = (tenant, feature) => {
  const planFeatures = {
    basic: ['inventory_tracking'],
    standard: ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'basic_reporting'],
    premium: ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'advanced_reporting', 'api_access', 'batch_operations', 'data_export'],
    enterprise: ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'advanced_reporting', 'api_access', 'batch_operations', 'data_export', 'custom_integrations']
  };

  return planFeatures[tenant.subscription.plan]?.includes(feature) || false;
};

// Update tenant usage statistics
const updateTenantUsage = async (tenant_id, usageType, increment = 1) => {
  try {
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) return null;

    switch (usageType) {
      case 'users':
        tenant.usage.users_count += increment;
        break;
      case 'products':
        tenant.usage.products_count += increment;
        break;
      case 'storage':
        tenant.usage.storage_used += increment;
        break;
      case 'api_calls':
        tenant.usage.api_calls_this_month += increment;
        break;
    }

    await tenant.save();
    return tenant;
  } catch (error) {
    console.error('Error updating tenant usage:', error);
    return null;
  }
};

// Check if tenant is within usage limits
const checkUsageLimits = async (tenant_id, limitType) => {
  try {
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) return { allowed: false, reason: 'Tenant not found' };

    const limits = tenant.limits;
    const usage = tenant.usage;

    switch (limitType) {
      case 'users':
        return {
          allowed: usage.users_count < limits.max_users,
          current: usage.users_count,
          limit: limits.max_users
        };
      case 'products':
        return {
          allowed: usage.products_count < limits.max_products,
          current: usage.products_count,
          limit: limits.max_products
        };
      case 'storage':
        return {
          allowed: usage.storage_used < limits.max_storage_mb,
          current: usage.storage_used,
          limit: limits.max_storage_mb
        };
      case 'api_calls':
        return {
          allowed: usage.api_calls_this_month < limits.max_api_calls_per_month,
          current: usage.api_calls_this_month,
          limit: limits.max_api_calls_per_month
        };
      default:
        return { allowed: false, reason: 'Invalid limit type' };
    }
  } catch (error) {
    console.error('Error checking usage limits:', error);
    return { allowed: false, reason: 'Error checking limits' };
  }
};

// Get tenant statistics
const getTenantStats = async (tenant_id) => {
  try {
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) return null;

    const userCount = await User.countDocuments({ tenant_id, status: 'active' });
    
    return {
      tenant_info: {
        tenant_id: tenant.tenant_id,
        business_name: tenant.business_name,
        subscription: tenant.subscription,
        status: tenant.status
      },
      usage: {
        users: {
          current: userCount,
          limit: tenant.limits.max_users,
          percentage: (userCount / tenant.limits.max_users) * 100
        },
        products: {
          current: tenant.usage.products_count,
          limit: tenant.limits.max_products,
          percentage: (tenant.usage.products_count / tenant.limits.max_products) * 100
        },
        storage: {
          current: tenant.usage.storage_used,
          limit: tenant.limits.max_storage_mb,
          percentage: (tenant.usage.storage_used / tenant.limits.max_storage_mb) * 100
        },
        api_calls: {
          current: tenant.usage.api_calls_this_month,
          limit: tenant.limits.max_api_calls_per_month,
          percentage: (tenant.usage.api_calls_this_month / tenant.limits.max_api_calls_per_month) * 100
        }
      },
      subscription: {
        plan: tenant.subscription.plan,
        status: tenant.subscription.status,
        end_date: tenant.subscription.end_date,
        days_until_expiry: tenant.subscription.end_date ? 
          Math.ceil((tenant.subscription.end_date - new Date()) / (1000 * 60 * 60 * 24)) : null
      }
    };
  } catch (error) {
    console.error('Error getting tenant stats:', error);
    return null;
  }
};

// Upgrade/downgrade tenant subscription
const updateSubscription = async (tenant_id, newPlan, stripeData = {}) => {
  try {
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) throw new Error('Tenant not found');

    const planLimits = {
      basic: {
        max_users: 1,
        max_products: 100,
        max_storage_mb: 1000,
        max_api_calls_per_month: 1000,
        monthly_price: 29
      },
      standard: {
        max_users: 3,
        max_products: -1, // unlimited
        max_storage_mb: 5000,
        max_api_calls_per_month: 10000,
        monthly_price: 79
      },
      premium: {
        max_users: -1, // unlimited
        max_products: -1,
        max_storage_mb: 20000,
        max_api_calls_per_month: 50000,
        monthly_price: 149
      },
      enterprise: {
        max_users: -1,
        max_products: -1,
        max_storage_mb: -1,
        max_api_calls_per_month: -1,
        monthly_price: 0 // custom pricing
      }
    };

    const newLimits = planLimits[newPlan];
    if (!newLimits) throw new Error('Invalid plan');

    // Update subscription
    tenant.subscription.plan = newPlan;
    tenant.subscription.monthly_price = newLimits.monthly_price;
    
    if (stripeData.stripe_subscription_id) {
      tenant.subscription.stripe_subscription_id = stripeData.stripe_subscription_id;
    }
    if (stripeData.stripe_customer_id) {
      tenant.subscription.stripe_customer_id = stripeData.stripe_customer_id;
    }

    // Update limits
    tenant.limits = newLimits;

    // Update features based on plan
    const planFeatures = {
      basic: [{ name: 'inventory_tracking', enabled: true }],
      standard: [
        { name: 'inventory_tracking', enabled: true },
        { name: 'pos_system', enabled: true },
        { name: 'supplier_management', enabled: true },
        { name: 'customer_management', enabled: true },
        { name: 'basic_reporting', enabled: true }
      ],
      premium: [
        { name: 'inventory_tracking', enabled: true },
        { name: 'pos_system', enabled: true },
        { name: 'supplier_management', enabled: true },
        { name: 'customer_management', enabled: true },
        { name: 'advanced_reporting', enabled: true },
        { name: 'api_access', enabled: true },
        { name: 'batch_operations', enabled: true },
        { name: 'data_export', enabled: true }
      ],
      enterprise: [
        { name: 'inventory_tracking', enabled: true },
        { name: 'pos_system', enabled: true },
        { name: 'supplier_management', enabled: true },
        { name: 'customer_management', enabled: true },
        { name: 'advanced_reporting', enabled: true },
        { name: 'api_access', enabled: true },
        { name: 'batch_operations', enabled: true },
        { name: 'data_export', enabled: true },
        { name: 'custom_integrations', enabled: true }
      ]
    };

    tenant.subscription.features = planFeatures[newPlan];

    await tenant.save();
    return tenant;
  } catch (error) {
    console.error('Error updating subscription:', error);
    throw error;
  }
};

// Suspend or activate tenant
const setTenantStatus = async (tenant_id, status, reason = '') => {
  try {
    const tenant = await Tenant.findOneAndUpdate(
      { tenant_id },
      { 
        status,
        updated_at: new Date(),
        // If suspending, also suspend subscription
        ...(status === 'suspended' && { 'subscription.status': 'suspended' }),
        ...(status === 'active' && { 'subscription.status': 'active' })
      },
      { new: true }
    );

    if (!tenant) throw new Error('Tenant not found');

    // Also update all users for this tenant
    await User.updateMany(
      { tenant_id },
      { 
        status: status === 'suspended' ? 'inactive' : 'active',
        updated_at: new Date()
      }
    );

    return tenant;
  } catch (error) {
    console.error('Error setting tenant status:', error);
    throw error;
  }
};

module.exports = {
  generateTenantId,
  hasFeatureAccess,
  updateTenantUsage,
  checkUsageLimits,
  getTenantStats,
  updateSubscription,
  setTenantStatus
};
