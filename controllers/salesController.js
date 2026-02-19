const Sale = require('../models/Sale');
const Product = require('../models/Product');
const InventoryBatch = require('../models/InventoryBatch');
const Customer = require('../models/Customer');
const { validationResult } = require('express-validator');
const { updateTenantUsage } = require('../utils/tenantUtils');

// Get sales history
const getSales = async (req, res) => {
  try {
    const { 
      page = 1, 
      limit = 50, 
      start_date, 
      end_date, 
      customer_id, 
      cashier_id,
      payment_method,
      status 
    } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    
    if (start_date || end_date) {
      query['transaction.date'] = {};
      if (start_date) query['transaction.date'].$gte = new Date(start_date);
      if (end_date) query['transaction.date'].$lte = new Date(end_date);
    }
    
    if (customer_id) query.customer_id = customer_id;
    if (cashier_id) query['staff.cashier_id'] = cashier_id;
    if (payment_method) query['payment.payment_method'] = payment_method;
    if (status) query.status = status;

    const sales = await Sale.find(query)
      .populate('customer_id', 'customer_code personal_info')
      .populate('staff.cashier_id', 'username profile')
      .sort({ 'transaction.date': -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Sale.countDocuments(query);

    res.json({
      success: true,
      data: {
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
    console.error('Get sales error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get sales',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get single sale
const getSale = async (req, res) => {
  try {
    const { sale_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const sale = await Sale.findOne({ tenant_id, _id: sale_id })
      .populate('customer_id', 'customer_code personal_info preferences')
      .populate('staff.cashier_id', 'username profile')
      .populate('items.product_id', 'name product_code category')
      .populate('items.batch_id', 'batch_number dates');

    if (!sale) {
      return res.status(404).json({
        success: false,
        message: 'Sale not found'
      });
    }

    res.json({
      success: true,
      data: { sale }
    });
  } catch (error) {
    console.error('Get sale error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get sale',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Process sale
const processSale = async (req, res) => {
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
      customer_id,
      customer_info,
      items,
      payment,
      notes,
      loyalty_member_id
    } = req.body;

    // Validate items and check inventory
    const processedItems = [];
    let totalAmount = 0;

    for (const item of items) {
      // Get product
      const product = await Product.findOne({ tenant_id, _id: item.product_id });
      if (!product) {
        return res.status(400).json({
          success: false,
          message: `Product not found: ${item.product_id}`
        });
      }

      // Get available batches for this product
      const availableBatches = await InventoryBatch.find({
        tenant_id,
        product_id: item.product_id,
        status: { $in: ['active', 'expiring_soon'] },
        'quantity.current_quantity': { $gt: 0 }
      }).sort({ 'dates.expiry_date': 1 }); // FIFO - use oldest batches first

      if (availableBatches.length === 0) {
        return res.status(400).json({
          success: false,
          message: `No inventory available for product: ${product.name}`
        });
      }

      // Calculate total quantity needed
      const totalQuantityNeeded = item.quantity.weight;
      let remainingQuantity = totalQuantityNeeded;
      const usedBatches = [];

      // Allocate inventory from batches (FIFO)
      for (const batch of availableBatches) {
        if (remainingQuantity <= 0) break;

        const availableQuantity = batch.quantity.current_quantity;
        const quantityFromBatch = Math.min(remainingQuantity, availableQuantity);

        usedBatches.push({
          batch_id: batch._id,
          quantity: quantityFromBatch,
          unit_cost: batch.cost.unit_cost
        });

        remainingQuantity -= quantityFromBatch;
      }

      if (remainingQuantity > 0) {
        return res.status(400).json({
          success: false,
          message: `Insufficient inventory for product: ${product.name}. Need ${totalQuantityNeeded}, available ${totalQuantityNeeded - remainingQuantity}`
        });
      }

      // Calculate item total
      const itemTotal = item.quantity.weight * product.pricing.price_per_unit;
      const taxAmount = itemTotal * (product.pricing.tax_rate / 100);
      const finalItemTotal = itemTotal + taxAmount;

      processedItems.push({
        product_id: item.product_id,
        batch_id: usedBatches[0].batch_id, // Primary batch for tracking
        product_name: product.name,
        product_code: product.product_code,
        quantity: item.quantity,
        pricing: {
          unit_price: product.pricing.price_per_unit,
          total_price: itemTotal,
          discount_amount: item.pricing?.discount_amount || 0,
          discount_type: item.pricing?.discount_type || 'fixed',
          tax_rate: product.pricing.tax_rate,
          tax_amount: taxAmount
        },
        quality_notes: item.quality_notes,
        special_instructions: item.special_instructions,
        used_batches: usedBatches // Store batch allocation info
      });

      totalAmount += finalItemTotal;
    }

    // Calculate payment totals
    const subtotal = processedItems.reduce((sum, item) => sum + item.pricing.total_price, 0);
    const totalTax = processedItems.reduce((sum, item) => sum + item.pricing.tax_amount, 0);
    const discountAmount = payment.discount_amount || 0;
    const finalTotal = subtotal + totalTax - discountAmount;

    // Create sale
    const sale = new Sale({
      tenant_id,
      customer_id,
      customer_info,
      items: processedItems,
      payment: {
        subtotal,
        discount_amount: discountAmount,
        tax_amount: totalTax,
        total_amount: finalTotal,
        amount_paid: payment.amount_paid || finalTotal,
        change_due: (payment.amount_paid || finalTotal) - finalTotal,
        payment_method: payment.payment_method,
        payment_status: payment.amount_paid >= finalTotal ? 'paid' : 'partial',
        card_details: payment.card_details
      },
      staff: {
        cashier_id: req.user._id,
        cashier_name: `${req.user.profile.first_name} ${req.user.profile.last_name}`
      },
      transaction: {
        date: new Date(),
        register_id: payment.register_id,
        terminal_id: payment.terminal_id,
        shift_id: payment.shift_id,
        is_offline: payment.is_offline || false
      },
      notes,
      loyalty: {
        loyalty_member_id,
        points_earned: Math.floor(finalTotal * 0.01), // 1 point per dollar
        points_redeemed: loyalty?.points_redeemed || 0
      }
    });

    await sale.save();

    // Update inventory
    for (const item of processedItems) {
      if (item.used_batches) {
        for (const batchInfo of item.used_batches) {
          await InventoryBatch.findByIdAndUpdate(
            batchInfo.batch_id,
            { 
              $inc: { 'quantity.current_quantity': -batchInfo.quantity },
              updated_at: new Date()
            }
          );
        }
      }

      // Update product inventory
      await Product.findByIdAndUpdate(
        item.product_id,
        { 
          $inc: { 'inventory.current_stock': -item.quantity.weight },
          updated_at: new Date()
        }
      );
    }

    // Update customer loyalty if applicable
    if (customer_id && sale.loyalty.points_earned > 0) {
      await Customer.findByIdAndUpdate(
        customer_id,
        {
          $inc: { 'loyalty.points_balance': sale.loyalty.points_earned },
          $set: { 'loyalty.last_activity': new Date() }
        }
      );
    }

    // Update tenant usage
    await updateTenantUsage(tenant_id, 'api_calls', 1);

    res.status(201).json({
      success: true,
      message: 'Sale processed successfully',
      data: { sale }
    });
  } catch (error) {
    console.error('Process sale error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to process sale',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Void sale
const voidSale = async (req, res) => {
  try {
    const { sale_id } = req.params;
    const { void_reason } = req.body;
    const tenant_id = req.user.tenant_id;

    const sale = await Sale.findOne({ tenant_id, _id: sale_id });
    if (!sale) {
      return res.status(404).json({
        success: false,
        message: 'Sale not found'
      });
    }

    if (sale.transaction.voided.is_voided) {
      return res.status(400).json({
        success: false,
        message: 'Sale is already voided'
      });
    }

    // Void the sale
    sale.voidSale(void_reason, req.user._id);
    await sale.save();

    // Restore inventory
    for (const item of sale.items) {
      // Restore to inventory batch
      await InventoryBatch.findByIdAndUpdate(
        item.batch_id,
        { 
          $inc: { 'quantity.current_quantity': item.quantity.weight },
          updated_at: new Date()
        }
      );

      // Restore to product inventory
      await Product.findByIdAndUpdate(
        item.product_id,
        { 
          $inc: { 'inventory.current_stock': item.quantity.weight },
          updated_at: new Date()
        }
      );
    }

    // Reverse customer loyalty points if applicable
    if (sale.customer_id && sale.loyalty.points_earned > 0) {
      await Customer.findByIdAndUpdate(
        sale.customer_id,
        {
          $inc: { 'loyalty.points_balance': -sale.loyalty.points_earned },
          $set: { 'loyalty.last_activity': new Date() }
        }
      );
    }

    res.json({
      success: true,
      message: 'Sale voided successfully',
      data: { sale }
    });
  } catch (error) {
    console.error('Void sale error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to void sale',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get sales summary
const getSalesSummary = async (req, res) => {
  try {
    const { start_date, end_date } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build date filter
    const dateFilter = {};
    if (start_date) dateFilter.$gte = new Date(start_date);
    if (end_date) dateFilter.$lte = new Date(end_date);

    const matchStage = { tenant_id };
    if (Object.keys(dateFilter).length > 0) {
      matchStage['transaction.date'] = dateFilter;
    }

    const [
      totalSales,
      salesByPaymentMethod,
      salesByCategory,
      topProducts,
      salesByHour,
      averageSale
    ] = await Promise.all([
      // Total sales and counts
      Sale.aggregate([
        { $match: matchStage },
        { $group: {
          _id: null,
          total_sales: { $sum: '$payment.total_amount' },
          total_orders: { $sum: 1 },
          average_order_value: { $avg: '$payment.total_amount' },
          total_tax: { $sum: '$payment.tax_amount' }
        }}
      ]),

      // Sales by payment method
      Sale.aggregate([
        { $match: matchStage },
        { $group: {
          _id: '$payment.payment_method',
          count: { $sum: 1 },
          total: { $sum: '$payment.total_amount' }
        }},
        { $sort: { total: -1 } }
      ]),

      // Sales by product category
      Sale.aggregate([
        { $match: matchStage },
        { $unwind: '$items' },
        { $lookup: {
          from: 'products',
          localField: 'items.product_id',
          foreignField: '_id',
          as: 'product'
        }},
        { $unwind: '$product' },
        { $group: {
          _id: '$product.category',
          total_sales: { $sum: '$items.pricing.total_price' },
          quantity_sold: { $sum: '$items.quantity.weight' },
          order_count: { $sum: 1 }
        }},
        { $sort: { total_sales: -1 } }
      ]),

      // Top selling products
      Sale.aggregate([
        { $match: matchStage },
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
          product_code: { $first: '$product.code' },
          total_quantity: { $sum: '$items.quantity.weight' },
          total_revenue: { $sum: '$items.pricing.total_price' },
          order_count: { $sum: 1 }
        }},
        { $sort: { total_revenue: -1 } },
        { $limit: 10 }
      ]),

      // Sales by hour
      Sale.aggregate([
        { $match: matchStage },
        { $project: {
          hour: { $hour: '$transaction.date' },
          total_amount: '$payment.total_amount'
        }},
        { $group: {
          _id: '$hour',
          total_sales: { $sum: '$total_amount' },
          order_count: { $sum: 1 }
        }},
        { $sort: { _id: 1 } }
      ]),

      // Average sale metrics
      Sale.aggregate([
        { $match: matchStage },
        { $group: {
          _id: null,
          avg_items_per_order: { $avg: { $size: '$items' } },
          avg_order_value: { $avg: '$payment.total_amount' },
          max_order_value: { $max: '$payment.total_amount' },
          min_order_value: { $min: '$payment.total_amount' }
        }}
      ])
    ]);

    const summary = {
      overview: totalSales[0] || {
        total_sales: 0,
        total_orders: 0,
        average_order_value: 0,
        total_tax: 0
      },
      payment_methods: salesByPaymentMethod,
      categories: salesByCategory,
      top_products: topProducts,
      hourly_sales: salesByHour,
      metrics: averageSale[0] || {
        avg_items_per_order: 0,
        avg_order_value: 0,
        max_order_value: 0,
        min_order_value: 0
      }
    };

    res.json({
      success: true,
      data: { summary }
    });
  } catch (error) {
    console.error('Get sales summary error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get sales summary',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get daily sales report
const getDailySalesReport = async (req, res) => {
  try {
    const { date } = req.query;
    const tenant_id = req.user.tenant_id;

    const targetDate = date ? new Date(date) : new Date();
    const startOfDay = new Date(targetDate.setHours(0, 0, 0, 0));
    const endOfDay = new Date(targetDate.setHours(23, 59, 59, 999));

    const sales = await Sale.find({
      tenant_id,
      'transaction.date': { $gte: startOfDay, $lte: endOfDay },
      status: 'completed'
    }).populate('customer_id', 'customer_code personal_info');

    const report = {
      date: targetDate.toISOString().split('T')[0],
      summary: {
        total_sales: sales.reduce((sum, sale) => sum + sale.payment.total_amount, 0),
        total_orders: sales.length,
        average_order_value: sales.length > 0 ? sales.reduce((sum, sale) => sum + sale.payment.total_amount, 0) / sales.length : 0,
        total_tax: sales.reduce((sum, sale) => sum + sale.payment.tax_amount, 0)
      },
      payment_breakdown: {},
      hourly_breakdown: {},
      sales: sales
    };

    // Calculate payment breakdown
    sales.forEach(sale => {
      const method = sale.payment.payment_method;
      if (!report.payment_breakdown[method]) {
        report.payment_breakdown[method] = { count: 0, total: 0 };
      }
      report.payment_breakdown[method].count++;
      report.payment_breakdown[method].total += sale.payment.total_amount;
    });

    // Calculate hourly breakdown
    sales.forEach(sale => {
      const hour = new Date(sale.transaction.date).getHours();
      if (!report.hourly_breakdown[hour]) {
        report.hourly_breakdown[hour] = { count: 0, total: 0 };
      }
      report.hourly_breakdown[hour].count++;
      report.hourly_breakdown[hour].total += sale.payment.total_amount;
    });

    res.json({
      success: true,
      data: { report }
    });
  } catch (error) {
    console.error('Get daily sales report error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get daily sales report',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getSales,
  getSale,
  processSale,
  voidSale,
  getSalesSummary,
  getDailySalesReport
};
