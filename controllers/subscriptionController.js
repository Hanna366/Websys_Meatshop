const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY);
const Tenant = require('../models/Tenant');
const { updateSubscription } = require('../utils/tenantUtils');
const { validationResult } = require('express-validator');

// Get subscription details
const getSubscription = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    const subscription = {
      current_plan: tenant.subscription.plan,
      status: tenant.subscription.status,
      start_date: tenant.subscription.start_date,
      end_date: tenant.subscription.end_date,
      monthly_price: tenant.subscription.monthly_price,
      features: tenant.subscription.features,
      stripe_customer_id: tenant.subscription.stripe_customer_id,
      stripe_subscription_id: tenant.subscription.stripe_subscription_id,
      usage: tenant.usage,
      limits: tenant.limits,
      days_until_expiry: tenant.subscription.end_date ? 
        Math.ceil((tenant.subscription.end_date - new Date()) / (1000 * 60 * 60 * 24)) : null
    };

    res.json({
      success: true,
      data: { subscription }
    });
  } catch (error) {
    console.error('Get subscription error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get subscription',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get available plans
const getPlans = async (req, res) => {
  try {
    const plans = [
      {
        id: 'basic',
        name: 'Basic',
        price: 29,
        description: 'Perfect for small shops with simple needs',
        features: [
          'Up to 100 products',
          'Inventory tracking and stock alerts',
          'Single user access',
          'Basic reporting',
          'Email support'
        ],
        limits: {
          max_users: 1,
          max_products: 100,
          max_storage_mb: 1000,
          max_api_calls_per_month: 1000
        },
        popular: false
      },
      {
        id: 'standard',
        name: 'Standard',
        price: 79,
        description: 'Great for growing businesses',
        features: [
          'Unlimited products',
          'Full POS system',
          'Supplier and customer management',
          'Basic reporting',
          'CSV export (limited)',
          'Up to 3 users',
          'Priority email support'
        ],
        limits: {
          max_users: 3,
          max_products: -1, // unlimited
          max_storage_mb: 5000,
          max_api_calls_per_month: 10000
        },
        popular: true
      },
      {
        id: 'premium',
        name: 'Premium',
        price: 149,
        description: 'For advanced operations',
        features: [
          'All Standard features',
          'Advanced analytics dashboard',
          'Unlimited data export (CSV, Excel, PDF)',
          'API access',
          'Batch operations',
          'Unlimited users',
          'Custom branding',
          'SMS notifications and priority support'
        ],
        limits: {
          max_users: -1, // unlimited
          max_products: -1,
          max_storage_mb: 20000,
          max_api_calls_per_month: 50000
        },
        popular: false
      },
      {
        id: 'enterprise',
        name: 'Enterprise',
        price: null,
        description: 'Custom solutions for large operations',
        features: [
          'All Premium features',
          'Dedicated database',
          'Custom integrations',
          'SLA and priority services',
          'On-premise deployment option',
          'Advanced compliance tools',
          'Dedicated account manager'
        ],
        limits: {
          max_users: -1,
          max_products: -1,
          max_storage_mb: -1,
          max_api_calls_per_month: -1
        },
        popular: false,
        custom_pricing: true
      }
    ];

    res.json({
      success: true,
      data: { plans }
    });
  } catch (error) {
    console.error('Get plans error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get plans',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create or update subscription
const createSubscription = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { plan_id, payment_method_id } = req.body;
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    // Validate plan
    const validPlans = ['basic', 'standard', 'premium'];
    if (!validPlans.includes(plan_id)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid plan selected'
      });
    }

    // Get plan pricing
    const planPrices = {
      basic: 2900, // $29.00 in cents
      standard: 7900, // $79.00 in cents
      premium: 14900 // $149.00 in cents
    };

    // Create or retrieve Stripe customer
    let stripeCustomerId = tenant.subscription.stripe_customer_id;
    
    if (!stripeCustomerId) {
      const customer = await stripe.customers.create({
        email: tenant.business_email,
        name: tenant.business_name,
        metadata: {
          tenant_id: tenant_id
        }
      });
      stripeCustomerId = customer.id;
    }

    // Create Stripe subscription
    const subscription = await stripe.subscriptions.create({
      customer: stripeCustomerId,
      items: [{
        price_data: {
          currency: 'usd',
          product_data: {
            name: `${plan_id.charAt(0).toUpperCase() + plan_id.slice(1)} Plan`,
            description: `Meat Shop POS ${plan_id.charAt(0).toUpperCase() + plan_id.slice(1)} subscription`
          },
          unit_amount: planPrices[plan_id],
          recurring: {
            interval: 'month'
          }
        }
      }],
      payment_behavior: 'default_incomplete',
      payment_settings: {
        save_default_payment_method: 'on_subscription',
        payment_method_types: ['card']
      },
      expand: ['latest_invoice.payment_intent'],
      metadata: {
        tenant_id: tenant_id,
        plan_id: plan_id
      }
    });

    // Update tenant subscription
    await updateSubscription(tenant_id, plan_id, {
      stripe_customer_id: stripeCustomerId,
      stripe_subscription_id: subscription.id
    });

    // Update subscription status
    tenant.subscription.status = subscription.status;
    tenant.subscription.stripe_subscription_id = subscription.id;
    tenant.subscription.stripe_customer_id = stripeCustomerId;
    await tenant.save();

    res.status(201).json({
      success: true,
      message: 'Subscription created successfully',
      data: {
        subscription_id: subscription.id,
        client_secret: subscription.latest_invoice.payment_intent.client_secret,
        plan: plan_id,
        status: subscription.status
      }
    });
  } catch (error) {
    console.error('Create subscription error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create subscription',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update subscription
const updateSubscriptionPlan = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { new_plan_id } = req.body;
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    if (!tenant.subscription.stripe_subscription_id) {
      return res.status(400).json({
        success: false,
        message: 'No active subscription found'
      });
    }

    // Get plan pricing
    const planPrices = {
      basic: 2900,
      standard: 7900,
      premium: 14900
    };

    // Retrieve Stripe subscription
    const subscription = await stripe.subscriptions.retrieve(tenant.subscription.stripe_subscription_id);

    // Update subscription item
    const updatedSubscription = await stripe.subscriptions.update(tenant.subscription.stripe_subscription_id, {
      items: [{
        id: subscription.items.data[0].id,
        price_data: {
          currency: 'usd',
          product_data: {
            name: `${new_plan_id.charAt(0).toUpperCase() + new_plan_id.slice(1)} Plan`
          },
          unit_amount: planPrices[new_plan_id],
          recurring: {
            interval: 'month'
          }
        }
      }],
      metadata: {
        tenant_id: tenant_id,
        plan_id: new_plan_id
      }
    });

    // Update tenant subscription
    await updateSubscription(tenant_id, new_plan_id);

    res.json({
      success: true,
      message: 'Subscription updated successfully',
      data: {
        plan: new_plan_id,
        status: updatedSubscription.status,
        current_period_end: new Date(updatedSubscription.current_period_end * 1000)
      }
    });
  } catch (error) {
    console.error('Update subscription error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update subscription',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Cancel subscription
const cancelSubscription = async (req, res) => {
  try {
    const { cancel_at_period_end = false, reason } = req.body;
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    if (!tenant.subscription.stripe_subscription_id) {
      return res.status(400).json({
        success: false,
        message: 'No active subscription found'
      });
    }

    // Cancel Stripe subscription
    const canceledSubscription = await stripe.subscriptions.update(
      tenant.subscription.stripe_subscription_id,
      {
        cancel_at_period_end: cancel_at_period_end,
        metadata: {
          cancellation_reason: reason || 'Customer requested'
        }
      }
    );

    // Update tenant subscription status
    tenant.subscription.status = cancel_at_period_end ? 'active' : 'cancelled';
    if (!cancel_at_period_end) {
      tenant.subscription.end_date = new Date();
    }
    await tenant.save();

    res.json({
      success: true,
      message: cancel_at_period_end ? 
        'Subscription will be cancelled at the end of the billing period' : 
        'Subscription cancelled immediately',
      data: {
        status: canceledSubscription.status,
        cancel_at_period_end: canceledSubscription.cancel_at_period_end,
        current_period_end: canceledSubscription.cancel_at_period_end ? 
          new Date(canceledSubscription.current_period_end * 1000) : null
      }
    });
  } catch (error) {
    console.error('Cancel subscription error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to cancel subscription',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get billing history
const getBillingHistory = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant || !tenant.subscription.stripe_customer_id) {
      return res.json({
        success: true,
        data: { invoices: [] }
      });
    }

    // Get invoices from Stripe
    const invoices = await stripe.invoices.list({
      customer: tenant.subscription.stripe_customer_id,
      limit: 50
    });

    const billingHistory = invoices.data.map(invoice => ({
      id: invoice.id,
      date: new Date(invoice.created * 1000),
      amount: invoice.total / 100,
      currency: invoice.currency.toUpperCase(),
      status: invoice.status,
      due_date: invoice.due_date ? new Date(invoice.due_date * 1000) : null,
      paid_at: invoice.status_transitions.paid_at ? 
        new Date(invoice.status_transitions.paid_at * 1000) : null,
      invoice_pdf: invoice.invoice_pdf,
      hosted_invoice_url: invoice.hosted_invoice_url
    }));

    res.json({
      success: true,
      data: { invoices: billingHistory }
    });
  } catch (error) {
    console.error('Get billing history error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get billing history',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update payment method
const updatePaymentMethod = async (req, res) => {
  try {
    const { payment_method_id } = req.body;
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant || !tenant.subscription.stripe_customer_id) {
      return res.status(400).json({
        success: false,
        message: 'No subscription found'
      });
    }

    // Attach payment method to customer
    await stripe.paymentMethods.attach(payment_method_id, {
      customer: tenant.subscription.stripe_customer_id
    });

    // Set as default payment method
    await stripe.customers.update(tenant.subscription.stripe_customer_id, {
      invoice_settings: {
        default_payment_method: payment_method_id
      }
    });

    res.json({
      success: true,
      message: 'Payment method updated successfully'
    });
  } catch (error) {
    console.error('Update payment method error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update payment method',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get usage statistics
const getUsageStats = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    const usageStats = {
      current_usage: tenant.usage,
      limits: tenant.limits,
      utilization: {
        users: {
          used: tenant.usage.users_count,
          limit: tenant.limits.max_users,
          percentage: tenant.limits.max_users > 0 ? 
            (tenant.usage.users_count / tenant.limits.max_users) * 100 : 0
        },
        products: {
          used: tenant.usage.products_count,
          limit: tenant.limits.max_products,
          percentage: tenant.limits.max_products > 0 ? 
            (tenant.usage.products_count / tenant.limits.max_products) * 100 : 0
        },
        storage: {
          used: tenant.usage.storage_used,
          limit: tenant.limits.max_storage_mb,
          percentage: tenant.limits.max_storage_mb > 0 ? 
            (tenant.usage.storage_used / tenant.limits.max_storage_mb) * 100 : 0
        },
        api_calls: {
          used: tenant.usage.api_calls_this_month,
          limit: tenant.limits.max_api_calls_per_month,
          percentage: tenant.limits.max_api_calls_per_month > 0 ? 
            (tenant.usage.api_calls_this_month / tenant.limits.max_api_calls_per_month) * 100 : 0
        }
      }
    };

    res.json({
      success: true,
      data: { usage_stats: usageStats }
    });
  } catch (error) {
    console.error('Get usage stats error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get usage statistics',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getSubscription,
  getPlans,
  createSubscription,
  updateSubscriptionPlan,
  cancelSubscription,
  getBillingHistory,
  updatePaymentMethod,
  getUsageStats
};
