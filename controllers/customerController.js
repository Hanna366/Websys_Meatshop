const Customer = require('../models/Customer');
const Sale = require('../models/Sale');
const { validationResult } = require('express-validator');

// Get all customers
const getCustomers = async (req, res) => {
  try {
    const { page = 1, limit = 50, status, loyalty_member, search } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    
    if (status) query.status = status;
    if (loyalty_member === 'true') query['loyalty.is_member'] = true;
    if (search) {
      query.$or = [
        { 'personal_info.first_name': { $regex: search, $options: 'i' } },
        { 'personal_info.last_name': { $regex: search, $options: 'i' } },
        { customer_code: { $regex: search, $options: 'i' } },
        { 'personal_info.email': { $regex: search, $options: 'i' } },
        { 'personal_info.phone': { $regex: search, $options: 'i' } }
      ];
    }

    const customers = await Customer.find(query)
      .sort({ 'personal_info.last_name': 1, 'personal_info.first_name': 1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Customer.countDocuments(query);

    // Add purchase statistics
    const customersWithStats = await Promise.all(
      customers.map(async (customer) => {
        const recentSales = await Sale.countDocuments({
          tenant_id,
          customer_id: customer._id,
          'transaction.date': { $gte: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000) }
        });

        return {
          ...customer.toObject(),
          stats: {
            recent_purchases: recentSales,
            lifetime_value: customer.lifetime_value,
            loyalty_tier: customer.loyalty.tier,
            points_balance: customer.loyalty.points_balance
          }
        };
      })
    );

    res.json({
      success: true,
      data: {
        customers: customersWithStats,
        pagination: {
          current_page: page,
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: limit
        }
      }
    });
  } catch (error) {
    console.error('Get customers error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get customers',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get single customer
const getCustomer = async (req, res) => {
  try {
    const { customer_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });

    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    // Get recent sales
    const recentSales = await Sale.find({
      tenant_id,
      customer_id: customer._id,
      status: 'completed'
    })
    .populate('items.product_id', 'name product_code')
    .sort({ 'transaction.date': -1 })
    .limit(10);

    // Get favorite products
    const favoriteProducts = customer.purchasing_history.favorite_products
      .sort((a, b) => b.total_spent - a.total_spent)
      .slice(0, 5);

    res.json({
      success: true,
      data: {
        customer,
        recent_sales: recentSales,
        favorite_products: favoriteProducts,
        stats: {
          lifetime_value: customer.lifetime_value,
          total_orders: customer.purchasing_history.total_orders,
          average_order_value: customer.purchasing_history.average_order_value,
          loyalty_tier: customer.loyalty.tier,
          points_balance: customer.loyalty.points_balance
        }
      }
    });
  } catch (error) {
    console.error('Get customer error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get customer',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create new customer
const createCustomer = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const tenant_id = req.user.tenant_id;

    // Check if customer code already exists
    const existingCustomer = await Customer.findOne({
      tenant_id,
      customer_code: req.body.customer_code
    });
    if (existingCustomer) {
      return res.status(400).json({
        success: false,
        message: 'Customer code already exists'
      });
    }

    // Check if email already exists
    if (req.body.personal_info.email) {
      const existingEmail = await Customer.findOne({
        tenant_id,
        'personal_info.email': req.body.personal_info.email
      });
      if (existingEmail) {
        return res.status(400).json({
          success: false,
          message: 'Email already registered'
        });
      }
    }

    // Generate customer code if not provided
    if (!req.body.customer_code) {
      const count = await Customer.countDocuments({ tenant_id });
      req.body.customer_code = `CUST${String(count + 1).padStart(6, '0')}`;
    }

    // Create customer
    const customer = new Customer({
      tenant_id,
      ...req.body,
      created_by: req.user._id
    });

    await customer.save();

    res.status(201).json({
      success: true,
      message: 'Customer created successfully',
      data: { customer }
    });
  } catch (error) {
    console.error('Create customer error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create customer',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update customer
const updateCustomer = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { customer_id } = req.params;
    const tenant_id = req.user.tenant_id;
    const updates = req.body;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    // Check if customer code is being changed and if it conflicts
    if (updates.customer_code && updates.customer_code !== customer.customer_code) {
      const existingCustomer = await Customer.findOne({
        tenant_id,
        customer_code: updates.customer_code,
        _id: { $ne: customer_id }
      });
      if (existingCustomer) {
        return res.status(400).json({
          success: false,
          message: 'Customer code already exists'
        });
      }
    }

    // Check if email is being changed and if it conflicts
    if (updates.personal_info?.email && updates.personal_info.email !== customer.personal_info.email) {
      const existingEmail = await Customer.findOne({
        tenant_id,
        'personal_info.email': updates.personal_info.email,
        _id: { $ne: customer_id }
      });
      if (existingEmail) {
        return res.status(400).json({
          success: false,
          message: 'Email already registered'
        });
      }
    }

    // Update customer
    Object.assign(customer, updates);
    customer.updated_by = req.user._id;
    await customer.save();

    res.json({
      success: true,
      message: 'Customer updated successfully',
      data: { customer }
    });
  } catch (error) {
    console.error('Update customer error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update customer',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Delete customer
const deleteCustomer = async (req, res) => {
  try {
    const { customer_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    // Check if customer has recent sales
    const recentSales = await Sale.countDocuments({
      tenant_id,
      customer_id: customer._id,
      'transaction.date': { $gte: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000) }
    });

    if (recentSales > 0) {
      return res.status(400).json({
        success: false,
        message: 'Cannot delete customer with recent sales history'
      });
    }

    // Soft delete by marking as inactive
    customer.status = 'inactive';
    customer.updated_by = req.user._id;
    await customer.save();

    res.json({
      success: true,
      message: 'Customer deleted successfully'
    });
  } catch (error) {
    console.error('Delete customer error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to delete customer',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Add loyalty points
const addLoyaltyPoints = async (req, res) => {
  try {
    const { customer_id } = req.params;
    const { points, reason } = req.body;
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    customer.addLoyaltyPoints(points, reason);
    customer.updated_by = req.user._id;
    await customer.save();

    res.json({
      success: true,
      message: 'Loyalty points added successfully',
      data: {
        points_added: points,
        new_balance: customer.loyalty.points_balance,
        tier: customer.loyalty.tier
      }
    });
  } catch (error) {
    console.error('Add loyalty points error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to add loyalty points',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Redeem loyalty points
const redeemLoyaltyPoints = async (req, res) => {
  try {
    const { customer_id } = req.params;
    const { points, reason } = req.body;
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    const redeemed = customer.redeemLoyaltyPoints(points, reason);
    if (!redeemed) {
      return res.status(400).json({
        success: false,
        message: 'Insufficient loyalty points'
      });
    }

    customer.updated_by = req.user._id;
    await customer.save();

    res.json({
      success: true,
      message: 'Loyalty points redeemed successfully',
      data: {
        points_redeemed: points,
        new_balance: customer.loyalty.points_balance,
        tier: customer.loyalty.tier
      }
    });
  } catch (error) {
    console.error('Redeem loyalty points error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to redeem loyalty points',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get customer purchase history
const getPurchaseHistory = async (req, res) => {
  try {
    const { customer_id } = req.params;
    const { page = 1, limit = 20, start_date, end_date } = req.query;
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    // Build query
    const query = { tenant_id, customer_id: customer._id, status: 'completed' };
    
    if (start_date || end_date) {
      query['transaction.date'] = {};
      if (start_date) query['transaction.date'].$gte = new Date(start_date);
      if (end_date) query['transaction.date'].$lte = new Date(end_date);
    }

    const sales = await Sale.find(query)
      .populate('items.product_id', 'name product_code category')
      .sort({ 'transaction.date': -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Sale.countDocuments(query);

    res.json({
      success: true,
      data: {
        customer: {
          customer_id: customer._id,
          customer_code: customer.customer_code,
          name: customer.full_name,
          loyalty_tier: customer.loyalty.tier,
          points_balance: customer.loyalty.points_balance
        },
        sales,
        pagination: {
          current_page: page,
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: limit
        }
      }
    });
  } catch (error) {
    console.error('Get purchase history error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get purchase history',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get customer analytics
const getCustomerAnalytics = async (req, res) => {
  try {
    const { customer_id } = req.params;
    const { period = '365' } = req.query; // days
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    const startDate = new Date(Date.now() - parseInt(period) * 24 * 60 * 60 * 1000);

    // Get sales analytics
    const salesAnalytics = await Sale.aggregate([
      { $match: { 
        tenant_id, 
        customer_id: customer._id,
        status: 'completed',
        'transaction.date': { $gte: startDate }
      }},
      { $group: {
        _id: null,
        total_orders: { $sum: 1 },
        total_spent: { $sum: '$payment.total_amount' },
        avg_order_value: { $avg: '$payment.total_amount' },
        total_items: { $sum: { $size: '$items' } },
        first_order_date: { $min: '$transaction.date' },
        last_order_date: { $max: '$transaction.date' }
      }}
    ]);

    // Get product preferences
    const productPreferences = await Sale.aggregate([
      { $match: { 
        tenant_id, 
        customer_id: customer._id,
        status: 'completed',
        'transaction.date': { $gte: startDate }
      }},
      { $unwind: '$items' },
      { $lookup: {
        from: 'products',
        localField: 'items.product_id',
        foreignField: '_id',
        as: 'product'
      }},
      { $unwind: '$product' },
      { $group: {
        _id: '$items.product_id',
        product_name: { $first: '$product.name' },
        product_category: { $first: '$product.category' },
        total_quantity: { $sum: '$items.quantity.weight' },
        total_spent: { $sum: '$items.pricing.total_price' },
        order_count: { $sum: 1 }
      }},
      { $sort: { total_spent: -1 } },
      { $limit: 10 }
    ]);

    // Get monthly spending trend
    const monthlyTrend = await Sale.aggregate([
      { $match: { 
        tenant_id, 
        customer_id: customer._id,
        status: 'completed',
        'transaction.date': { $gte: startDate }
      }},
      { $group: {
        _id: { 
          year: { $year: '$transaction.date' },
          month: { $month: '$transaction.date' }
        },
        total_spent: { $sum: '$payment.total_amount' },
        order_count: { $sum: 1 }
      }},
      { $sort: { '_id.year': 1, '_id.month': 1 } }
    ]);

    const analytics = {
      overview: salesAnalytics[0] || {
        total_orders: 0,
        total_spent: 0,
        avg_order_value: 0,
        total_items: 0
      },
      product_preferences,
      monthly_trend: monthlyTrend,
      loyalty_info: {
        tier: customer.loyalty.tier,
        points_balance: customer.loyalty.points_balance,
        points_earned: customer.loyalty.points_earned,
        points_redeemed: customer.loyalty.points_redeemed,
        member_since: customer.loyalty.join_date
      },
      period_days: parseInt(period)
    };

    res.json({
      success: true,
      data: { analytics }
    });
  } catch (error) {
    console.error('Get customer analytics error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get customer analytics',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getCustomers,
  getCustomer,
  createCustomer,
  updateCustomer,
  deleteCustomer,
  addLoyaltyPoints,
  redeemLoyaltyPoints,
  getPurchaseHistory,
  getCustomerAnalytics
};
