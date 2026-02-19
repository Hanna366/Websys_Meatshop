const Product = require('../models/Product');
const InventoryBatch = require('../models/InventoryBatch');
const Sale = require('../models/Sale');
const Customer = require('../models/Customer');
const Supplier = require('../models/Supplier');
const { validationResult } = require('express-validator');
const { updateTenantUsage } = require('../utils/tenantUtils');

// API rate limiting middleware
const checkApiRateLimit = async (req, res, next) => {
  try {
    const tenant_id = req.user.tenant_id;
    const Tenant = require('../models/Tenant');
    
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    // Check API access feature
    if (!tenant.subscription.features.some(f => f.name === 'api_access' && f.enabled)) {
      return res.status(403).json({
        success: false,
        message: 'API access not available in current plan'
      });
    }

    // Check API rate limit
    if (tenant.usage.api_calls_this_month >= tenant.limits.max_api_calls_per_month) {
      return res.status(429).json({
        success: false,
        message: 'API rate limit exceeded'
      });
    }

    // Update API usage
    await updateTenantUsage(tenant_id, 'api_calls', 1);
    
    next();
  } catch (error) {
    console.error('API rate limit check error:', error);
    res.status(500).json({
      success: false,
      message: 'API rate limit check failed'
    });
  }
};

// Get API documentation
const getApiDocs = async (req, res) => {
  try {
    const apiDocs = {
      title: 'Meat Shop POS API',
      version: '1.0.0',
      description: 'RESTful API for Meat Shop POS System',
      base_url: `${req.protocol}://${req.get('host')}/api/v1`,
      authentication: {
        type: 'Bearer Token',
        description: 'Include JWT token in Authorization header'
      },
      endpoints: {
        products: {
          get: {
            path: '/products',
            description: 'Get all products',
            parameters: {
              page: 'Page number (default: 1)',
              limit: 'Items per page (default: 50, max: 100)',
              category: 'Filter by category',
              status: 'Filter by status',
              search: 'Search term'
            },
            response: 'Array of products'
          },
          get_by_id: {
            path: '/products/{id}',
            description: 'Get single product by ID',
            parameters: {
              id: 'Product ID'
            },
            response: 'Product object'
          },
          create: {
            path: '/products',
            method: 'POST',
            description: 'Create new product',
            body: 'Product object',
            response: 'Created product'
          },
          update: {
            path: '/products/{id}',
            method: 'PUT',
            description: 'Update product',
            parameters: {
              id: 'Product ID'
            },
            body: 'Product updates',
            response: 'Updated product'
          }
        },
        inventory: {
          get_batches: {
            path: '/inventory/batches',
            description: 'Get inventory batches',
            parameters: {
              product_id: 'Filter by product ID',
              status: 'Filter by status'
            },
            response: 'Array of inventory batches'
          },
          update_batch: {
            path: '/inventory/batches/{id}',
            method: 'PUT',
            description: 'Update inventory batch',
            parameters: {
              id: 'Batch ID'
            },
            body: 'Batch updates',
            response: 'Updated batch'
          }
        },
        sales: {
          get: {
            path: '/sales',
            description: 'Get sales',
            parameters: {
              start_date: 'Filter by start date',
              end_date: 'Filter by end date',
              page: 'Page number',
              limit: 'Items per page'
            },
            response: 'Array of sales'
          },
          create: {
            path: '/sales',
            method: 'POST',
            description: 'Create new sale',
            body: 'Sale object',
            response: 'Created sale'
          }
        },
        customers: {
          get: {
            path: '/customers',
            description: 'Get customers',
            parameters: {
              page: 'Page number',
              limit: 'Items per page',
              search: 'Search term'
            },
            response: 'Array of customers'
          },
          create: {
            path: '/customers',
            method: 'POST',
            description: 'Create new customer',
            body: 'Customer object',
            response: 'Created customer'
          }
        }
      },
      rate_limits: {
        description: 'API calls are limited based on subscription plan',
        plans: {
          basic: '1,000 calls/month',
          standard: '10,000 calls/month',
          premium: '50,000 calls/month',
          enterprise: 'Unlimited'
        }
      },
      errors: {
        400: 'Bad Request - Validation error',
        401: 'Unauthorized - Invalid or missing token',
        403: 'Forbidden - Insufficient permissions or plan',
        404: 'Not Found - Resource not found',
        429: 'Too Many Requests - Rate limit exceeded',
        500: 'Internal Server Error'
      }
    };

    res.json({
      success: true,
      data: { api_docs: apiDocs }
    });
  } catch (error) {
    console.error('Get API docs error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get API documentation',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get products via API
const getApiProducts = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { page = 1, limit = 50, category, status, search } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    if (category) query.category = category;
    if (status) query.status = status;
    if (search) {
      query.$or = [
        { name: { $regex: search, $options: 'i' } },
        { product_code: { $regex: search, $options: 'i' } },
        { description: { $regex: search, $options: 'i' } }
      ];
    }

    const products = await Product.find(query)
      .select('-__v -created_by -updated_by')
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Product.countDocuments(query);

    res.json({
      success: true,
      data: {
        products,
        pagination: {
          current_page: parseInt(page),
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: parseInt(limit)
        }
      }
    });
  } catch (error) {
    console.error('Get API products error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get products',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create product via API
const createApiProduct = async (req, res) => {
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

    // Check if product code already exists
    const existingProduct = await Product.findOne({
      tenant_id,
      product_code: req.body.product_code
    });
    if (existingProduct) {
      return res.status(400).json({
        success: false,
        message: 'Product code already exists'
      });
    }

    // Create product
    const product = new Product({
      tenant_id,
      ...req.body,
      created_by: req.user._id
    });

    await product.save();

    res.status(201).json({
      success: true,
      message: 'Product created successfully',
      data: { 
        product: product.toObject({ 
          transform: (doc, ret) => { 
            delete ret.__v; 
            delete ret.created_by; 
            delete ret.updated_by; 
            return ret; 
          } 
        })
      }
    });
  } catch (error) {
    console.error('Create API product error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create product',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get inventory batches via API
const getApiInventoryBatches = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { page = 1, limit = 50, product_id, status } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    if (product_id) query.product_id = product_id;
    if (status) query.status = status;

    const batches = await InventoryBatch.find(query)
      .select('-__v -created_by -updated_by')
      .populate('product_id', 'name product_code')
      .populate('supplier_id', 'business_name supplier_code')
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await InventoryBatch.countDocuments(query);

    res.json({
      success: true,
      data: {
        batches,
        pagination: {
          current_page: parseInt(page),
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: parseInt(limit)
        }
      }
    });
  } catch (error) {
    console.error('Get API inventory batches error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get inventory batches',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create sale via API
const createApiSale = async (req, res) => {
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
    const { items, payment, customer_id } = req.body;

    // Validate items and check inventory
    const processedItems = [];
    let totalAmount = 0;

    for (const item of items) {
      const product = await Product.findOne({ tenant_id, _id: item.product_id });
      if (!product) {
        return res.status(400).json({
          success: false,
          message: `Product not found: ${item.product_id}`
        });
      }

      const availableBatches = await InventoryBatch.find({
        tenant_id,
        product_id: item.product_id,
        status: { $in: ['active', 'expiring_soon'] },
        'quantity.current_quantity': { $gt: 0 }
      }).sort({ 'dates.expiry_date': 1 });

      if (availableBatches.length === 0) {
        return res.status(400).json({
          success: false,
          message: `No inventory available for product: ${product.name}`
        });
      }

      const totalQuantityNeeded = item.quantity.weight;
      let remainingQuantity = totalQuantityNeeded;

      for (const batch of availableBatches) {
        if (remainingQuantity <= 0) break;
        const availableQuantity = batch.quantity.current_quantity;
        const quantityFromBatch = Math.min(remainingQuantity, availableQuantity);
        remainingQuantity -= quantityFromBatch;
      }

      if (remainingQuantity > 0) {
        return res.status(400).json({
          success: false,
          message: `Insufficient inventory for product: ${product.name}`
        });
      }

      const itemTotal = item.quantity.weight * product.pricing.price_per_unit;
      const taxAmount = itemTotal * (product.pricing.tax_rate / 100);

      processedItems.push({
        product_id: item.product_id,
        batch_id: availableBatches[0]._id,
        product_name: product.name,
        product_code: product.product_code,
        quantity: item.quantity,
        pricing: {
          unit_price: product.pricing.price_per_unit,
          total_price: itemTotal,
          tax_rate: product.pricing.tax_rate,
          tax_amount: taxAmount
        }
      });

      totalAmount += itemTotal + taxAmount;
    }

    // Create sale
    const Sale = require('../models/Sale');
    const sale = new Sale({
      tenant_id,
      customer_id,
      items: processedItems,
      payment: {
        subtotal: processedItems.reduce((sum, item) => sum + item.pricing.total_price, 0),
        tax_amount: processedItems.reduce((sum, item) => sum + item.pricing.tax_amount, 0),
        total_amount: totalAmount,
        payment_method: payment.payment_method,
        payment_status: 'paid'
      },
      staff: {
        cashier_id: req.user._id,
        cashier_name: `${req.user.profile.first_name} ${req.user.profile.last_name}`
      },
      transaction: {
        date: new Date(),
        is_offline: false
      }
    });

    await sale.save();

    // Update inventory
    for (const item of processedItems) {
      await InventoryBatch.findByIdAndUpdate(
        item.batch_id,
        { 
          $inc: { 'quantity.current_quantity': -item.quantity.weight },
          updated_at: new Date()
        }
      );

      await Product.findByIdAndUpdate(
        item.product_id,
        { 
          $inc: { 'inventory.current_stock': -item.quantity.weight },
          updated_at: new Date()
        }
      );
    }

    res.status(201).json({
      success: true,
      message: 'Sale created successfully',
      data: { 
        sale: sale.toObject({ 
          transform: (doc, ret) => { 
            delete ret.__v; 
            return ret; 
          } 
        })
      }
    });
  } catch (error) {
    console.error('Create API sale error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create sale',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get customers via API
const getApiCustomers = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { page = 1, limit = 50, search } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    if (search) {
      query.$or = [
        { 'personal_info.first_name': { $regex: search, $options: 'i' } },
        { 'personal_info.last_name': { $regex: search, $options: 'i' } },
        { customer_code: { $regex: search, $options: 'i' } },
        { 'personal_info.email': { $regex: search, $options: 'i' } }
      ];
    }

    const customers = await Customer.find(query)
      .select('-__v -created_by -updated_by')
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Customer.countDocuments(query);

    res.json({
      success: true,
      data: {
        customers,
        pagination: {
          current_page: parseInt(page),
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: parseInt(limit)
        }
      }
    });
  } catch (error) {
    console.error('Get API customers error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get customers',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create customer via API
const createApiCustomer = async (req, res) => {
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
      data: { 
        customer: customer.toObject({ 
          transform: (doc, ret) => { 
            delete ret.__v; 
            delete ret.created_by; 
            delete ret.updated_by; 
            return ret; 
          } 
        })
      }
    });
  } catch (error) {
    console.error('Create API customer error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create customer',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get API usage statistics
const getApiUsage = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;
    const Tenant = require('../models/Tenant');
    
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    const usage = {
      current_month: {
        calls_made: tenant.usage.api_calls_this_month,
        limit: tenant.limits.max_api_calls_per_month,
        percentage: tenant.limits.max_api_calls_per_month > 0 ? 
          (tenant.usage.api_calls_this_month / tenant.limits.max_api_calls_per_month) * 100 : 0
      },
      plan_limits: {
        max_calls_per_month: tenant.limits.max_api_calls_per_month,
        plan: tenant.subscription.plan
      }
    };

    res.json({
      success: true,
      data: { usage }
    });
  } catch (error) {
    console.error('Get API usage error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get API usage',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  checkApiRateLimit,
  getApiDocs,
  getApiProducts,
  createApiProduct,
  getApiInventoryBatches,
  createApiSale,
  getApiCustomers,
  createApiCustomer,
  getApiUsage
};
