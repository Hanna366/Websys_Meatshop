const Sale = require('../models/Sale');
const Product = require('../models/Product');
const InventoryBatch = require('../models/InventoryBatch');
const Customer = require('../models/Customer');
const Supplier = require('../models/Supplier');
const { validationResult } = require('express-validator');

// Get dashboard overview
const getDashboardOverview = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;
    const { period = '30' } = req.query; // days

    const startDate = new Date(Date.now() - parseInt(period) * 24 * 60 * 60 * 1000);

    const [
      salesSummary,
      inventorySummary,
      customerSummary,
      supplierSummary,
      recentSales,
      lowStockProducts,
      expiringBatches
    ] = await Promise.all([
      // Sales summary
      Sale.aggregate([
        { $match: { 
          tenant_id,
          status: 'completed',
          'transaction.date': { $gte: startDate }
        }},
        { $group: {
          _id: null,
          total_sales: { $sum: '$payment.total_amount' },
          total_orders: { $sum: 1 },
          average_order_value: { $avg: '$payment.total_amount' },
          total_tax: { $sum: '$payment.tax_amount' },
          total_customers: { $addToSet: '$customer_id' }
        }},
        { $project: {
          total_sales: 1,
          total_orders: 1,
          average_order_value: 1,
          total_tax: 1,
          unique_customers: { $size: '$total_customers' }
        }}
      ]),

      // Inventory summary
      Product.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $group: {
          _id: null,
          total_products: { $sum: 1 },
          total_stock_value: { $sum: { $multiply: ['$inventory.current_stock', '$pricing.price_per_unit'] } },
          low_stock_count: {
            $sum: { $cond: [{ $lte: ['$inventory.current_stock', '$inventory.reorder_level'] }, 1, 0] }
          }
        }}
      ]),

      // Customer summary
      Customer.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $group: {
          _id: null,
          total_customers: { $sum: 1 },
          loyalty_members: { $sum: { $cond: ['$loyalty.is_member', 1, 0] } },
          total_points_balance: { $sum: '$loyalty.points_balance' }
        }}
      ]),

      // Supplier summary
      Supplier.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $group: {
          _id: null,
          total_suppliers: { $sum: 1 },
          preferred_suppliers: { $sum: { $cond: ['$preferences.preferred_supplier', 1, 0] } },
          average_quality_score: { $avg: '$quality_standards.quality_score' }
        }}
      ]),

      // Recent sales
      Sale.find({
        tenant_id,
        status: 'completed'
      })
      .populate('customer_id', 'customer_code personal_info')
      .sort({ 'transaction.date': -1 })
      .limit(5),

      // Low stock products
      Product.find({
        tenant_id,
        status: 'active',
        '$expr': { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] }
      })
      .sort({ 'inventory.current_stock': 1 })
      .limit(5),

      // Expiring batches
      InventoryBatch.find({
        tenant_id,
        status: { $in: ['active', 'expiring_soon'] },
        'dates.expiry_date': { $lte: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) }
      })
      .populate('product_id', 'name product_code')
      .sort({ 'dates.expiry_date': 1 })
      .limit(5)
    ]);

    const overview = {
      sales: salesSummary[0] || {
        total_sales: 0,
        total_orders: 0,
        average_order_value: 0,
        total_tax: 0,
        unique_customers: 0
      },
      inventory: inventorySummary[0] || {
        total_products: 0,
        total_stock_value: 0,
        low_stock_count: 0
      },
      customers: customerSummary[0] || {
        total_customers: 0,
        loyalty_members: 0,
        total_points_balance: 0
      },
      suppliers: supplierSummary[0] || {
        total_suppliers: 0,
        preferred_suppliers: 0,
        average_quality_score: 0
      },
      recent_sales: recentSales,
      alerts: {
        low_stock_products: lowStockProducts,
        expiring_batches: expiringBatches
      },
      period_days: parseInt(period)
    };

    res.json({
      success: true,
      data: { overview }
    });
  } catch (error) {
    console.error('Get dashboard overview error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get dashboard overview',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get sales report
const getSalesReport = async (req, res) => {
  try {
    const { 
      start_date, 
      end_date, 
      group_by = 'day', 
      customer_id, 
      product_id,
      category 
    } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build date filter
    const dateFilter = {};
    if (start_date) dateFilter.$gte = new Date(start_date);
    if (end_date) dateFilter.$lte = new Date(end_date);

    const matchStage = { 
      tenant_id,
      status: 'completed'
    };
    if (Object.keys(dateFilter).length > 0) {
      matchStage['transaction.date'] = dateFilter;
    }
    if (customer_id) matchStage.customer_id = customer_id;

    // Group by date format
    let groupFormat;
    switch (group_by) {
      case 'hour':
        groupFormat = { $dateToString: { format: '%Y-%m-%d %H:00', date: '$transaction.date' } };
        break;
      case 'day':
        groupFormat = { $dateToString: { format: '%Y-%m-%d', date: '$transaction.date' } };
        break;
      case 'week':
        groupFormat = { $dateToString: { format: '%Y-W%U', date: '$transaction.date' } };
        break;
      case 'month':
        groupFormat = { $dateToString: { format: '%Y-%m', date: '$transaction.date' } };
        break;
      default:
        groupFormat = { $dateToString: { format: '%Y-%m-%d', date: '$transaction.date' } };
    }

    const [salesByPeriod, topProducts, paymentBreakdown] = await Promise.all([
      // Sales by time period
      Sale.aggregate([
        { $match: matchStage },
        ...(product_id ? [{
          $unwind: '$items'
        }, {
          $match: { 'items.product_id': product_id }
        }] : []),
        ...(category ? [{
          $lookup: {
            from: 'products',
            localField: 'items.product_id',
            foreignField: '_id',
            as: 'product'
          }
        }, {
          $unwind: '$product'
        }, {
          $match: { 'product.category': category }
        }] : []),
        { $group: {
          _id: groupFormat,
          total_sales: { $sum: '$payment.total_amount' },
          total_orders: { $sum: 1 },
          average_order_value: { $avg: '$payment.total_amount' },
          total_tax: { $sum: '$payment.tax_amount' }
        }},
        { $sort: { _id: 1 } }
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
        ...(category ? [{ $match: { 'product.category': category } }] : []),
        { $group: {
          _id: '$items.product_id',
          product_name: { $first: '$product.name' },
          product_code: { $first: '$product.product_code' },
          category: { $first: '$product.category' },
          total_quantity: { $sum: '$items.quantity.weight' },
          total_revenue: { $sum: '$items.pricing.total_price' },
          order_count: { $sum: 1 }
        }},
        { $sort: { total_revenue: -1 } },
        { $limit: 10 }
      ]),

      // Payment method breakdown
      Sale.aggregate([
        { $match: matchStage },
        { $group: {
          _id: '$payment.payment_method',
          count: { $sum: 1 },
          total: { $sum: '$payment.total_amount' }
        }},
        { $sort: { total: -1 } }
      ])
    ]);

    const report = {
      sales_by_period: salesByPeriod,
      top_products: topProducts,
      payment_breakdown: paymentBreakdown,
      filters: {
        start_date,
        end_date,
        group_by,
        customer_id,
        product_id,
        category
      }
    };

    res.json({
      success: true,
      data: { report }
    });
  } catch (error) {
    console.error('Get sales report error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get sales report',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get inventory report
const getInventoryReport = async (req, res) => {
  try {
    const { 
      category, 
      status, 
      include_valuation = true,
      include_movements = false 
    } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const productQuery = { tenant_id };
    if (category) productQuery.category = category;
    if (status) productQuery.status = status;

    const [inventorySummary, categoryBreakdown, lowStockItems, wasteReport] = await Promise.all([
      // Overall inventory summary
      Product.aggregate([
        { $match: productQuery },
        { $group: {
          _id: null,
          total_products: { $sum: 1 },
          total_stock_quantity: { $sum: '$inventory.current_stock' },
          total_stock_value: { $sum: { $multiply: ['$inventory.current_stock', '$pricing.price_per_unit'] } },
          low_stock_count: {
            $sum: { $cond: [{ $lte: ['$inventory.current_stock', '$inventory.reorder_level'] }, 1, 0] }
          },
          out_of_stock_count: {
            $sum: { $cond: [{ $eq: ['$inventory.current_stock', 0] }, 1, 0] }
          }
        }}
      ]),

      // Category breakdown
      Product.aggregate([
        { $match: productQuery },
        { $group: {
          _id: '$category',
          product_count: { $sum: 1 },
          total_quantity: { $sum: '$inventory.current_stock' },
          total_value: { $sum: { $multiply: ['$inventory.current_stock', '$pricing.price_per_unit'] } },
          average_price: { $avg: '$pricing.price_per_unit' }
        }},
        { $sort: { total_value: -1 } }
      ]),

      // Low stock items
      Product.find({
        ...productQuery,
        status: 'active',
        '$expr': { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] }
      })
      .populate('supplier_info.primary_supplier_id', 'business_name')
      .sort({ 'inventory.current_stock': 1 }),

      // Waste report
      InventoryBatch.aggregate([
        { $match: { 
          tenant_id,
          'waste_tracking.waste_quantity': { $gt: 0 }
        }},
        { $lookup: {
          from: 'products',
          localField: 'product_id',
          foreignField: '_id',
          as: 'product'
        }},
        { $unwind: '$product' },
        ...(category ? [{ $match: { 'product.category': category } }] : []),
        { $group: {
          _id: {
            product_id: '$product_id',
            waste_reason: '$waste_tracking.waste_reason'
          },
          product_name: { $first: '$product.name' },
          product_code: { $first: '$product.product_code' },
          category: { $first: '$product.category' },
          total_waste_quantity: { $sum: '$waste_tracking.waste_quantity' },
          total_waste_value: { $sum: { $multiply: ['$waste_tracking.waste_quantity', '$cost.unit_cost'] } },
          waste_count: { $sum: 1 }
        }},
        { $sort: { total_waste_value: -1 } }
      ])
    ]);

    // Batch status summary
    const batchStatus = await InventoryBatch.aggregate([
      { $match: { tenant_id }},
      { $lookup: {
        from: 'products',
        localField: 'product_id',
        foreignField: '_id',
        as: 'product'
      }},
      { $unwind: '$product' },
      ...(category ? [{ $match: { 'product.category': category } }] : []),
      { $group: {
        _id: '$status',
        count: { $sum: 1 },
        total_quantity: { $sum: '$quantity.current_quantity' },
        total_value: { $sum: { $multiply: ['$quantity.current_quantity', '$cost.unit_cost'] } }
      }}
    ]);

    const report = {
      summary: inventorySummary[0] || {
        total_products: 0,
        total_stock_quantity: 0,
        total_stock_value: 0,
        low_stock_count: 0,
        out_of_stock_count: 0
      },
      category_breakdown: categoryBreakdown,
      batch_status: batchStatus,
      low_stock_items: lowStockItems,
      waste_report: wasteReport,
      filters: {
        category,
        status,
        include_valuation,
        include_movements
      }
    };

    res.json({
      success: true,
      data: { report }
    });
  } catch (error) {
    console.error('Get inventory report error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get inventory report',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get customer analytics report
const getCustomerReport = async (req, res) => {
  try {
    const { 
      start_date, 
      end_date, 
      loyalty_tier,
      group_by = 'acquisition' 
    } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build date filter
    const dateFilter = {};
    if (start_date) dateFilter.$gte = new Date(start_date);
    if (end_date) dateFilter.$lte = new Date(end_date);

    const [customerSummary, loyaltyBreakdown, topCustomers, acquisitionTrend] = await Promise.all([
      // Customer summary
      Customer.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $group: {
          _id: null,
          total_customers: { $sum: 1 },
          loyalty_members: { $sum: { $cond: ['$loyalty.is_member', 1, 0] } },
          total_points_balance: { $sum: '$loyalty.points_balance' },
          total_lifetime_value: { $sum: '$purchasing_history.total_spent' },
          average_lifetime_value: { $avg: '$purchasing_history.total_spent' }
        }}
      ]),

      // Loyalty tier breakdown
      Customer.aggregate([
        { $match: { 
          tenant_id, 
          status: 'active',
          ...(loyalty_tier && { 'loyalty.tier': loyalty_tier })
        }},
        { $group: {
          _id: '$loyalty.tier',
          count: { $sum: 1 },
          total_points: { $sum: '$loyalty.points_balance' },
          total_spent: { $sum: '$purchasing_history.total_spent' },
          average_spent: { $avg: '$purchasing_history.total_spent' }
        }},
        { $sort: { count: -1 } }
      ]),

      // Top customers by spending
      Customer.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $sort: { 'purchasing_history.total_spent': -1 }},
        { $limit: 10 },
        { $project: {
          customer_id: '$_id',
          customer_code: 1,
          full_name: { $concat: ['$personal_info.first_name', ' ', '$personal_info.last_name'] },
          email: '$personal_info.email',
          phone: '$personal_info.phone',
          loyalty_tier: '$loyalty.tier',
          points_balance: '$loyalty.points_balance',
          total_orders: '$purchasing_history.total_orders',
          total_spent: '$purchasing_history.total_spent',
          average_order_value: '$purchasing_history.average_order_value',
          last_purchase_date: '$purchasing_history.last_purchase_date'
        }}
      ]),

      // Customer acquisition trend
      Sale.aggregate([
        { $match: { 
          tenant_id,
          status: 'completed',
          ...(Object.keys(dateFilter).length > 0 && { 'transaction.date': dateFilter })
        }},
        { $group: {
          _id: {
            year: { $year: '$transaction.date' },
            month: { $month: '$transaction.date' }
          },
          new_customers: { $addToSet: '$customer_id' },
          total_orders: { $sum: 1 }
        }},
        { $project: {
          period: { $concat: [
            { $toString: '$_id.year' },
            '-',
            { $toString: { $cond: [{ $lt: ['$_id.month', 10] }, { $concat: ['0', { $toString: '$_id.month' }] }, { $toString: '$_id.month' }] }
          ]},
          new_customers: { $size: '$new_customers' },
          total_orders: '$total_orders'
        }},
        { $sort: { period: 1 } }
      ])
    ]);

    // Customer segmentation
    const segmentation = await Customer.aggregate([
      { $match: { tenant_id, status: 'active' }},
      { $bucket: {
        groupBy: '$purchasing_history.total_spent',
        boundaries: [0, 100, 500, 1000, 5000, Infinity],
        default: 'Other',
        output: {
          count: { $sum: 1 },
          total_spent: { $sum: '$purchasing_history.total_spent' },
          customers: { $push: {
            customer_id: '$_id',
            full_name: { $concat: ['$personal_info.first_name', ' ', '$personal_info.last_name'] },
            total_spent: '$purchasing_history.total_spent'
          }}
        }
      }}
    ]);

    const report = {
      summary: customerSummary[0] || {
        total_customers: 0,
        loyalty_members: 0,
        total_points_balance: 0,
        total_lifetime_value: 0,
        average_lifetime_value: 0
      },
      loyalty_breakdown: loyaltyBreakdown,
      top_customers: topCustomers,
      acquisition_trend: acquisitionTrend,
      segmentation: segmentation.map((seg, index) => ({
        segment: ['0-100', '100-500', '500-1000', '1000-5000', '5000+'][index] || 'Other',
        ...seg
      })),
      filters: {
        start_date,
        end_date,
        loyalty_tier,
        group_by
      }
    };

    res.json({
      success: true,
      data: { report }
    });
  } catch (error) {
    console.error('Get customer report error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get customer report',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get supplier performance report
const getSupplierReport = async (req, res) => {
  try {
    const { 
      start_date, 
      end_date, 
      category,
      include_quality = true 
    } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build date filter
    const dateFilter = {};
    if (start_date) dateFilter.$gte = new Date(start_date);
    if (end_date) dateFilter.$lte = new Date(end_date);

    const [supplierSummary, supplierRankings, categoryPerformance, qualityMetrics] = await Promise.all([
      // Supplier summary
      Supplier.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $group: {
          _id: null,
          total_suppliers: { $sum: 1 },
          preferred_suppliers: { $sum: { $cond: ['$preferences.preferred_supplier', 1, 0] } },
          average_quality_score: { $avg: '$quality_standards.quality_score' },
          total_orders: { $sum: '$performance.total_orders' },
          total_value: { $sum: '$performance.total_value' }
        }}
      ]),

      // Supplier rankings
      Supplier.aggregate([
        { $match: { tenant_id, status: 'active' }},
        { $lookup: {
          from: 'inventorybatches',
          localField: '_id',
          foreignField: 'supplier_id',
          as: 'batches'
        }},
        { $project: {
          supplier_id: '$_id',
          supplier_code: 1,
          business_name: 1,
          quality_score: '$quality_standards.quality_score',
          total_batches: { $size: '$batches' },
          recent_batches: {
            $size: {
              $filter: {
                input: '$batches',
                cond: { 
                  $gte: ['$$this.dates.received_date', new Date(Date.now() - 90 * 24 * 60 * 60 * 1000)]
                }
              }
            }
          },
          is_preferred: '$preferences.preferred_supplier'
        }}
      ]),
      
      // Category performance by supplier
      InventoryBatch.aggregate([
        { $match: { 
          tenant_id,
          ...(Object.keys(dateFilter).length > 0 && { 'dates.received_date': dateFilter })
        }},
        { $lookup: {
          from: 'products',
          localField: 'product_id',
          foreignField: '_id',
          as: 'product'
        }},
        { $unwind: '$product' },
        ...(category ? [{ $match: { 'product.category': category } }] : []),
        { $lookup: {
          from: 'suppliers',
          localField: 'supplier_id',
          foreignField: '_id',
          as: 'supplier'
        }},
        { $unwind: '$supplier' },
        { $group: {
          _id: {
            supplier_id: '$supplier_id',
            category: '$product.category'
          },
          supplier_name: { $first: '$supplier.business_name' },
          total_quantity: { $sum: '$quantity.initial_quantity' },
          total_value: { $sum: '$cost.total_cost' },
          batch_count: { $sum: 1 },
          average_quality_score: { $avg: { $cond: ['$quality.inspection_passed', 1, 0] } }
        }},
        { $sort: { total_value: -1 } }
      ]),

      // Quality metrics
      ...(include_quality ? [InventoryBatch.aggregate([
        { $match: { 
          tenant_id,
          ...(Object.keys(dateFilter).length > 0 && { 'dates.received_date': dateFilter })
        }},
        { $lookup: {
          from: 'suppliers',
          localField: 'supplier_id',
          foreignField: '_id',
          as: 'supplier'
        }},
        { $unwind: '$supplier' },
        { $group: {
          _id: '$supplier_id',
          supplier_name: { $first: '$supplier.business_name' },
          total_batches: { $sum: 1 },
          passed_inspections: { $sum: { $cond: ['$quality.inspection_passed', 1, 0] } },
          failed_inspections: { $sum: { $cond: ['$quality.inspection_passed', 0, 1] } },
          waste_quantity: { $sum: '$waste_tracking.waste_quantity' },
          average_quality_score: { $avg: { $cond: ['$quality.inspection_passed', 1, 0] } }
        }},
        { $sort: { average_quality_score: -1 } }
      ])] : [[]])
    ]);

    const report = {
      summary: supplierSummary[0] || {
        total_suppliers: 0,
        preferred_suppliers: 0,
        average_quality_score: 0,
        total_orders: 0,
        total_value: 0
      },
      supplier_rankings: supplierRankings.sort((a, b) => b.quality_score - a.quality_score),
      category_performance: categoryPerformance,
      quality_metrics: qualityMetrics,
      filters: {
        start_date,
        end_date,
        category,
        include_quality
      }
    };

    res.json({
      success: true,
      data: { report }
    });
  } catch (error) {
    console.error('Get supplier report error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get supplier report',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Export report data
const exportReport = async (req, res) => {
  try {
    const { report_type, format = 'csv', ...filters } = req.query;
    const tenant_id = req.user.tenant_id;

    let data;
    let filename;

    switch (report_type) {
      case 'sales':
        data = await getSalesReportData(tenant_id, filters);
        filename = `sales_report_${new Date().toISOString().split('T')[0]}.${format}`;
        break;
      case 'inventory':
        data = await getInventoryReportData(tenant_id, filters);
        filename = `inventory_report_${new Date().toISOString().split('T')[0]}.${format}`;
        break;
      case 'customers':
        data = await getCustomerReportData(tenant_id, filters);
        filename = `customer_report_${new Date().toISOString().split('T')[0]}.${format}`;
        break;
      case 'suppliers':
        data = await getSupplierReportData(tenant_id, filters);
        filename = `supplier_report_${new Date().toISOString().split('T')[0]}.${format}`;
        break;
      default:
        return res.status(400).json({
          success: false,
          message: 'Invalid report type'
        });
    }

    // Generate file based on format
    let fileContent;
    let contentType;

    if (format === 'csv') {
      fileContent = generateCSV(data);
      contentType = 'text/csv';
    } else if (format === 'json') {
      fileContent = JSON.stringify(data, null, 2);
      contentType = 'application/json';
    } else {
      return res.status(400).json({
        success: false,
        message: 'Unsupported format'
      });
    }

    res.setHeader('Content-Type', contentType);
    res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
    res.send(fileContent);
  } catch (error) {
    console.error('Export report error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to export report',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Helper functions for data extraction
async function getSalesReportData(tenant_id, filters) {
  // Implementation for sales data extraction
  return [];
}

async function getInventoryReportData(tenant_id, filters) {
  // Implementation for inventory data extraction
  return [];
}

async function getCustomerReportData(tenant_id, filters) {
  // Implementation for customer data extraction
  return [];
}

async function getSupplierReportData(tenant_id, filters) {
  // Implementation for supplier data extraction
  return [];
}

function generateCSV(data) {
  // Simple CSV generation - in production, use a proper CSV library
  if (!data || data.length === 0) return '';
  
  const headers = Object.keys(data[0]);
  const csvRows = [headers.join(',')];
  
  for (const row of data) {
    const values = headers.map(header => {
      const value = row[header];
      return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
    });
    csvRows.push(values.join(','));
  }
  
  return csvRows.join('\n');
}

module.exports = {
  getDashboardOverview,
  getSalesReport,
  getInventoryReport,
  getCustomerReport,
  getSupplierReport,
  exportReport
};
