const Product = require('../models/Product');
const InventoryBatch = require('../models/InventoryBatch');
const { validationResult } = require('express-validator');
const { updateTenantUsage, checkUsageLimits } = require('../utils/tenantUtils');

// Get all inventory items for a tenant
const getInventory = async (req, res) => {
  try {
    const { page = 1, limit = 50, category, status, low_stock } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    
    if (category) query.category = category;
    if (status) query.status = status;
    if (low_stock === 'true') {
      query['$expr'] = { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] };
    }

    const products = await Product.find(query)
      .populate('supplier_info.primary_supplier_id', 'business_name supplier_code')
      .sort({ 'inventory.current_stock': 1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Product.countDocuments(query);

    // Get batch information for each product
    const productsWithBatches = await Promise.all(
      products.map(async (product) => {
        const batches = await InventoryBatch.find({
          tenant_id,
          product_id: product._id,
          status: { $in: ['active', 'expiring_soon'] }
        }).sort({ 'dates.expiry_date': 1 });

        return {
          ...product.toObject(),
          batches,
          total_stock: batches.reduce((sum, batch) => sum + batch.quantity.current_quantity, 0),
          expiring_soon_count: batches.filter(batch => batch.isExpiringSoon(7)).length,
          expired_count: batches.filter(batch => batch.isExpired()).length
        };
      })
    );

    res.json({
      success: true,
      data: {
        products: productsWithBatches,
        pagination: {
          current_page: page,
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: limit
        }
      }
    });
  } catch (error) {
    console.error('Get inventory error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get inventory',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get inventory batches for a specific product
const getProductBatches = async (req, res) => {
  try {
    const { product_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const batches = await InventoryBatch.find({
      tenant_id,
      product_id,
      status: { $in: ['active', 'expiring_soon'] }
    })
    .populate('supplier_id', 'business_name supplier_code')
    .populate('created_by', 'username')
    .sort({ 'dates.expiry_date': 1 });

    const product = await Product.findOne({ tenant_id, _id: product_id });

    if (!product) {
      return res.status(404).json({
        success: false,
        message: 'Product not found'
      });
    }

    res.json({
      success: true,
      data: {
        product,
        batches,
        total_quantity: batches.reduce((sum, batch) => sum + batch.quantity.current_quantity, 0),
        total_value: batches.reduce((sum, batch) => sum + (batch.quantity.current_quantity * batch.cost.unit_cost), 0)
      }
    });
  } catch (error) {
    console.error('Get product batches error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get product batches',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Add new inventory batch
const addInventoryBatch = async (req, res) => {
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
    const {
      product_id,
      batch_number,
      supplier_id,
      quantity,
      cost,
      dates,
      quality,
      storage,
      tracking
    } = req.body;

    // Check if product exists
    const product = await Product.findOne({ tenant_id, _id: product_id });
    if (!product) {
      return res.status(404).json({
        success: false,
        message: 'Product not found'
      });
    }

    // Check if batch number already exists
    const existingBatch = await InventoryBatch.findOne({
      tenant_id,
      batch_number
    });
    if (existingBatch) {
      return res.status(400).json({
        success: false,
        message: 'Batch number already exists'
      });
    }

    // Create new inventory batch
    const batch = new InventoryBatch({
      tenant_id,
      product_id,
      batch_number,
      supplier_id,
      quantity: {
        initial_quantity: quantity.initial_quantity,
        current_quantity: quantity.initial_quantity,
        unit: quantity.unit
      },
      cost: {
        unit_cost: cost.unit_cost,
        total_cost: cost.unit_cost * quantity.initial_quantity,
        currency: cost.currency || 'USD'
      },
      dates: {
        received_date: dates.received_date || new Date(),
        production_date: dates.production_date,
        expiry_date: dates.expiry_date,
        freeze_date: dates.freeze_date,
        thaw_date: dates.thaw_date
      },
      quality: quality || {},
      storage: storage || {},
      tracking: tracking || {},
      created_by: req.user._id
    });

    await batch.save();

    // Update product stock
    product.inventory.current_stock += quantity.initial_quantity;
    await product.save();

    // Update tenant usage
    await updateTenantUsage(tenant_id, 'storage', 1); // Increment storage usage

    res.status(201).json({
      success: true,
      message: 'Inventory batch added successfully',
      data: { batch }
    });
  } catch (error) {
    console.error('Add inventory batch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to add inventory batch',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update inventory batch
const updateInventoryBatch = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { batch_id } = req.params;
    const tenant_id = req.user.tenant_id;
    const updates = req.body;

    const batch = await InventoryBatch.findOne({ tenant_id, _id: batch_id });
    if (!batch) {
      return res.status(404).json({
        success: false,
        message: 'Batch not found'
      });
    }

    // Update batch
    Object.assign(batch, updates);
    batch.updated_by = req.user._id;
    await batch.save();

    // Recalculate product stock
    const allBatches = await InventoryBatch.find({
      tenant_id,
      product_id: batch.product_id,
      status: { $in: ['active', 'expiring_soon'] }
    });

    const totalStock = allBatches.reduce((sum, b) => sum + b.quantity.current_quantity, 0);
    
    await Product.findByIdAndUpdate(
      batch.product_id,
      { 'inventory.current_stock': totalStock }
    );

    res.json({
      success: true,
      message: 'Inventory batch updated successfully',
      data: { batch }
    });
  } catch (error) {
    console.error('Update inventory batch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update inventory batch',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Record waste/loss for inventory batch
const recordWaste = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { batch_id } = req.params;
    const tenant_id = req.user.tenant_id;
    const { waste_quantity, waste_reason, waste_notes } = req.body;

    const batch = await InventoryBatch.findOne({ tenant_id, _id: batch_id });
    if (!batch) {
      return res.status(404).json({
        success: false,
        message: 'Batch not found'
      });
    }

    // Check if enough quantity available
    if (waste_quantity > batch.quantity.current_quantity) {
      return res.status(400).json({
        success: false,
        message: 'Waste quantity exceeds available stock'
      });
    }

    // Record waste
    batch.recordWaste(waste_quantity, waste_reason, req.user._id, waste_notes);
    batch.updated_by = req.user._id;
    await batch.save();

    // Update product stock
    const product = await Product.findById(batch.product_id);
    product.inventory.current_stock -= waste_quantity;
    await product.save();

    res.json({
      success: true,
      message: 'Waste recorded successfully',
      data: { batch }
    });
  } catch (error) {
    console.error('Record waste error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to record waste',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get inventory alerts
const getInventoryAlerts = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;
    const { alert_type } = req.query;

    let alerts = [];

    // Low stock alerts
    if (!alert_type || alert_type === 'low_stock') {
      const lowStockProducts = await Product.find({
        tenant_id,
        status: 'active',
        '$expr': { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] }
      }).populate('supplier_info.primary_supplier_id', 'business_name');

      lowStockProducts.forEach(product => {
        alerts.push({
          type: 'low_stock',
          severity: 'medium',
          product_id: product._id,
          product_name: product.name,
          product_code: product.product_code,
          current_stock: product.inventory.current_stock,
          reorder_level: product.inventory.reorder_level,
          supplier: product.supplier_info.primary_supplier_id?.business_name || 'No supplier',
          created_at: new Date()
        });
      });
    }

    // Expiry alerts
    if (!alert_type || alert_type === 'expiry') {
      const expiringBatches = await InventoryBatch.find({
        tenant_id,
        status: { $in: ['active', 'expiring_soon'] },
        'dates.expiry_date': { $lte: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) }
      })
      .populate('product_id', 'name product_code')
      .populate('supplier_id', 'business_name');

      expiringBatches.forEach(batch => {
        const daysToExpiry = batch.daysUntilExpiry;
        const severity = daysToExpiry <= 0 ? 'critical' : daysToExpiry <= 3 ? 'high' : 'medium';

        alerts.push({
          type: 'expiry',
          severity,
          batch_id: batch._id,
          batch_number: batch.batch_number,
          product_name: batch.product_id.name,
          product_code: batch.product_id.product_code,
          quantity: batch.quantity.current_quantity,
          expiry_date: batch.dates.expiry_date,
          days_until_expiry: daysToExpiry,
          supplier: batch.supplier_id.business_name,
          created_at: new Date()
        });
      });
    }

    // Quality alerts
    if (!alert_type || alert_type === 'quality') {
      const qualityIssueBatches = await InventoryBatch.find({
        tenant_id,
        'quality.inspection_passed': false,
        status: { $ne: 'depleted' }
      })
      .populate('product_id', 'name product_code')
      .populate('supplier_id', 'business_name');

      qualityIssueBatches.forEach(batch => {
        alerts.push({
          type: 'quality',
          severity: 'high',
          batch_id: batch._id,
          batch_number: batch.batch_number,
          product_name: batch.product_id.name,
          product_code: batch.product_id.product_code,
          quantity: batch.quantity.current_quantity,
          inspection_notes: batch.quality.inspection_notes,
          supplier: batch.supplier_id.business_name,
          created_at: new Date()
        });
      });
    }

    // Sort alerts by severity and date
    const severityOrder = { critical: 0, high: 1, medium: 2, low: 3 };
    alerts.sort((a, b) => {
      if (severityOrder[a.severity] !== severityOrder[b.severity]) {
        return severityOrder[a.severity] - severityOrder[b.severity];
      }
      return new Date(b.created_at) - new Date(a.created_at);
    });

    res.json({
      success: true,
      data: {
        alerts,
        summary: {
          total: alerts.length,
          critical: alerts.filter(a => a.severity === 'critical').length,
          high: alerts.filter(a => a.severity === 'high').length,
          medium: alerts.filter(a => a.severity === 'medium').length,
          low: alerts.filter(a => a.severity === 'low').length
        }
      }
    });
  } catch (error) {
    console.error('Get inventory alerts error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get inventory alerts',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get inventory statistics
const getInventoryStats = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const [
      totalProducts,
      activeProducts,
      lowStockCount,
      totalValue,
      expiringSoon,
      expired,
      wasteStats
    ] = await Promise.all([
      Product.countDocuments({ tenant_id }),
      Product.countDocuments({ tenant_id, status: 'active' }),
      Product.countDocuments({
        tenant_id,
        status: 'active',
        '$expr': { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] }
      }),
      InventoryBatch.aggregate([
        { $match: { tenant_id, status: { $in: ['active', 'expiring_soon'] } } },
        { $group: { _id: null, total: { $sum: { $multiply: ['$quantity.current_quantity', '$cost.unit_cost'] } } } }
      ]),
      InventoryBatch.countDocuments({
        tenant_id,
        status: 'expiring_soon'
      }),
      InventoryBatch.countDocuments({
        tenant_id,
        status: 'expired'
      }),
      InventoryBatch.aggregate([
        { $match: { tenant_id, 'waste_tracking.waste_quantity': { $gt: 0 } } },
        { $group: { 
          _id: null, 
          totalWaste: { $sum: '$waste_tracking.waste_quantity' },
          totalWasteValue: { $sum: { $multiply: ['$waste_tracking.waste_quantity', '$cost.unit_cost'] } }
        }}
      ])
    ]);

    const stats = {
      overview: {
        total_products: totalProducts,
        active_products: activeProducts,
        low_stock_products: lowStockCount,
        total_inventory_value: totalValue[0]?.total || 0
      },
      batch_status: {
        expiring_soon: expiringSoon,
        expired: expired,
        active_batches: await InventoryBatch.countDocuments({
          tenant_id,
          status: 'active'
        })
      },
      waste_tracking: {
        total_waste_quantity: wasteStats[0]?.totalWaste || 0,
        total_waste_value: wasteStats[0]?.totalWasteValue || 0
      }
    };

    res.json({
      success: true,
      data: { stats }
    });
  } catch (error) {
    console.error('Get inventory stats error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get inventory statistics',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getInventory,
  getProductBatches,
  addInventoryBatch,
  updateInventoryBatch,
  recordWaste,
  getInventoryAlerts,
  getInventoryStats
};
