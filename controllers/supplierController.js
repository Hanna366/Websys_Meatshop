const Supplier = require('../models/Supplier');
const InventoryBatch = require('../models/InventoryBatch');
const { validationResult } = require('express-validator');

// Get all suppliers
const getSuppliers = async (req, res) => {
  try {
    const { page = 1, limit = 50, status, preferred, search } = req.query;
    const tenant_id = req.user.tenant_id;

    // Build query
    const query = { tenant_id };
    
    if (status) query.status = status;
    if (preferred === 'true') query['preferences.preferred_supplier'] = true;
    if (search) {
      query.$or = [
        { business_name: { $regex: search, $options: 'i' } },
        { supplier_code: { $regex: search, $options: 'i' } },
        { 'contact_info.primary_contact.name': { $regex: search, $options: 'i' } },
        { 'contact_info.primary_contact.email': { $regex: search, $options: 'i' } }
      ];
    }

    const suppliers = await Supplier.find(query)
      .sort({ business_name: 1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Supplier.countDocuments(query);

    // Add performance metrics
    const suppliersWithMetrics = await Promise.all(
      suppliers.map(async (supplier) => {
        const recentBatches = await InventoryBatch.countDocuments({
          tenant_id,
          supplier_id: supplier._id,
          'dates.received_date': { $gte: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000) }
        });

        const totalBatches = await InventoryBatch.countDocuments({
          tenant_id,
          supplier_id: supplier._id
        });

        return {
          ...supplier.toObject(),
          metrics: {
            recent_batches: recentBatches,
            total_batches: totalBatches,
            quality_score: supplier.quality_standards.quality_score,
            overall_rating: supplier.overallRating
          }
        };
      })
    );

    res.json({
      success: true,
      data: {
        suppliers: suppliersWithMetrics,
        pagination: {
          current_page: page,
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: limit
        }
      }
    });
  } catch (error) {
    console.error('Get suppliers error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get suppliers',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get single supplier
const getSupplier = async (req, res) => {
  try {
    const { supplier_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const supplier = await Supplier.findOne({ tenant_id, _id: supplier_id });

    if (!supplier) {
      return res.status(404).json({
        success: false,
        message: 'Supplier not found'
      });
    }

    // Get recent batches from this supplier
    const recentBatches = await InventoryBatch.find({
      tenant_id,
      supplier_id: supplier._id,
      'dates.received_date': { $gte: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000) }
    })
    .populate('product_id', 'name product_code category')
    .sort({ 'dates.received_date': -1 })
    .limit(10);

    // Get performance statistics
    const performanceStats = await InventoryBatch.aggregate([
      { $match: { tenant_id, supplier_id: supplier._id } },
      { $group: {
        _id: null,
        total_batches: { $sum: 1 },
        total_quantity: { $sum: '$quantity.initial_quantity' },
        total_value: { $sum: '$cost.total_cost' },
        avg_quality_score: { $avg: '$quality.inspection_passed' }
      }}
    ]);

    res.json({
      success: true,
      data: {
        supplier,
        recent_batches: recentBatches,
        performance_stats: performanceStats[0] || {
          total_batches: 0,
          total_quantity: 0,
          total_value: 0,
          avg_quality_score: 0
        }
      }
    });
  } catch (error) {
    console.error('Get supplier error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get supplier',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create new supplier
const createSupplier = async (req, res) => {
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

    // Check if supplier code already exists
    const existingSupplier = await Supplier.findOne({
      tenant_id,
      supplier_code: req.body.supplier_code
    });
    if (existingSupplier) {
      return res.status(400).json({
        success: false,
        message: 'Supplier code already exists'
      });
    }

    // Create supplier
    const supplier = new Supplier({
      tenant_id,
      ...req.body,
      created_by: req.user._id
    });

    await supplier.save();

    res.status(201).json({
      success: true,
      message: 'Supplier created successfully',
      data: { supplier }
    });
  } catch (error) {
    console.error('Create supplier error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create supplier',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update supplier
const updateSupplier = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { supplier_id } = req.params;
    const tenant_id = req.user.tenant_id;
    const updates = req.body;

    const supplier = await Supplier.findOne({ tenant_id, _id: supplier_id });
    if (!supplier) {
      return res.status(404).json({
        success: false,
        message: 'Supplier not found'
      });
    }

    // Check if supplier code is being changed and if it conflicts
    if (updates.supplier_code && updates.supplier_code !== supplier.supplier_code) {
      const existingSupplier = await Supplier.findOne({
        tenant_id,
        supplier_code: updates.supplier_code,
        _id: { $ne: supplier_id }
      });
      if (existingSupplier) {
        return res.status(400).json({
          success: false,
          message: 'Supplier code already exists'
        });
      }
    }

    // Update supplier
    Object.assign(supplier, updates);
    supplier.updated_by = req.user._id;
    await supplier.save();

    res.json({
      success: true,
      message: 'Supplier updated successfully',
      data: { supplier }
    });
  } catch (error) {
    console.error('Update supplier error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update supplier',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Delete supplier
const deleteSupplier = async (req, res) => {
  try {
    const { supplier_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const supplier = await Supplier.findOne({ tenant_id, _id: supplier_id });
    if (!supplier) {
      return res.status(404).json({
        success: false,
        message: 'Supplier not found'
      });
    }

    // Check if supplier has active inventory batches
    const activeBatches = await InventoryBatch.countDocuments({
      tenant_id,
      supplier_id: supplier._id,
      status: { $in: ['active', 'expiring_soon'] }
    });

    if (activeBatches > 0) {
      return res.status(400).json({
        success: false,
        message: 'Cannot delete supplier with active inventory batches'
      });
    }

    // Soft delete by marking as inactive
    supplier.status = 'inactive';
    supplier.updated_by = req.user._id;
    await supplier.save();

    res.json({
      success: true,
      message: 'Supplier deleted successfully'
    });
  } catch (error) {
    console.error('Delete supplier error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to delete supplier',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update supplier quality score
const updateQualityScore = async (req, res) => {
  try {
    const { supplier_id } = req.params;
    const { quality_score, inspection_notes } = req.body;
    const tenant_id = req.user.tenant_id;

    const supplier = await Supplier.findOne({ tenant_id, _id: supplier_id });
    if (!supplier) {
      return res.status(404).json({
        success: false,
        message: 'Supplier not found'
      });
    }

    // Update quality score
    supplier.updateQualityScore(quality_score);
    supplier.quality_standards.last_inspection_date = new Date();
    if (inspection_notes) {
      supplier.quality_standards.inspection_notes = inspection_notes;
    }
    supplier.updated_by = req.user._id;
    await supplier.save();

    res.json({
      success: true,
      message: 'Quality score updated successfully',
      data: { 
        quality_score: supplier.quality_standards.quality_score,
        overall_rating: supplier.overallRating
      }
    });
  } catch (error) {
    console.error('Update quality score error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update quality score',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get supplier performance metrics
const getSupplierPerformance = async (req, res) => {
  try {
    const { supplier_id } = req.params;
    const { period = '90' } = req.query; // days
    const tenant_id = req.user.tenant_id;

    const startDate = new Date(Date.now() - parseInt(period) * 24 * 60 * 60 * 1000);

    const supplier = await Supplier.findOne({ tenant_id, _id: supplier_id });
    if (!supplier) {
      return res.status(404).json({
        success: false,
        message: 'Supplier not found'
      });
    }

    // Get batch performance data
    const batchMetrics = await InventoryBatch.aggregate([
      { $match: { 
        tenant_id, 
        supplier_id: supplier._id,
        'dates.received_date': { $gte: startDate }
      }},
      { $group: {
        _id: null,
        total_batches: { $sum: 1 },
        total_quantity: { $sum: '$quantity.initial_quantity' },
        total_value: { $sum: '$cost.total_cost' },
        avg_quality_score: { $avg: { $cond: ['$quality.inspection_passed', 1, 0] } },
        expired_batches: {
          $sum: { $cond: [{ $lt: ['$dates.expiry_date', new Date()] }, 1, 0] }
        },
        waste_quantity: { $sum: '$waste_tracking.waste_quantity' }
      }}
    ]);

    // Get product categories supplied
    const categories = await InventoryBatch.aggregate([
      { $match: { 
        tenant_id, 
        supplier_id: supplier._id,
        'dates.received_date': { $gte: startDate }
      }},
      { $lookup: {
        from: 'products',
        localField: 'product_id',
        foreignField: '_id',
        as: 'product'
      }},
      { $unwind: '$product' },
      { $group: {
        _id: '$product.category',
        total_quantity: { $sum: '$quantity.initial_quantity' },
        total_value: { $sum: '$cost.total_cost' },
        batch_count: { $sum: 1 }
      }},
      { $sort: { total_value: -1 } }
    ]);

    // Calculate delivery performance (mock data for now)
    const deliveryPerformance = {
      on_time_delivery_rate: 95.5, // This would be calculated from actual delivery data
      average_lead_time: 2.5, // days
      order_accuracy_rate: 98.2
    };

    const performance = {
      overview: batchMetrics[0] || {
        total_batches: 0,
        total_quantity: 0,
        total_value: 0,
        avg_quality_score: 0,
        expired_batches: 0,
        waste_quantity: 0
      },
      categories,
      delivery_performance,
      quality_metrics: {
        quality_score: supplier.quality_standards.quality_score,
        overall_rating: supplier.overallRating,
        last_inspection_date: supplier.quality_standards.last_inspection_date
      },
      period_days: parseInt(period)
    };

    res.json({
      success: true,
      data: { performance }
    });
  } catch (error) {
    console.error('Get supplier performance error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get supplier performance',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get supplier rankings
const getSupplierRankings = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const suppliers = await Supplier.find({ 
      tenant_id, 
      status: 'active' 
    });

    // Calculate rankings based on various metrics
    const supplierRankings = await Promise.all(
      suppliers.map(async (supplier) => {
        const recentBatches = await InventoryBatch.countDocuments({
          tenant_id,
          supplier_id: supplier._id,
          'dates.received_date': { $gte: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000) }
        });

        const totalValue = await InventoryBatch.aggregate([
          { $match: { 
            tenant_id, 
            supplier_id: supplier._id,
            'dates.received_date': { $gte: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000) }
          }},
          { $group: { _id: null, total: { $sum: '$cost.total_cost' } } }
        ]);

        return {
          supplier_id: supplier._id,
          supplier_code: supplier.supplier_code,
          business_name: supplier.business_name,
          quality_score: supplier.quality_standards.quality_score,
          overall_rating: supplier.overallRating,
          recent_batches: recentBatches,
          total_value_90days: totalValue[0]?.total || 0,
          is_preferred: supplier.preferences.preferred_supplier
        };
      })
    );

    // Sort by overall rating, then by total value
    supplierRankings.sort((a, b) => {
      if (b.overall_rating !== a.overall_rating) {
        return b.overall_rating - a.overall_rating;
      }
      return b.total_value_90days - a.total_value_90days;
    });

    res.json({
      success: true,
      data: { 
        rankings: supplierRankings.map((supplier, index) => ({
          ...supplier,
          rank: index + 1
        }))
      }
    });
  } catch (error) {
    console.error('Get supplier rankings error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get supplier rankings',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getSuppliers,
  getSupplier,
  createSupplier,
  updateSupplier,
  deleteSupplier,
  updateQualityScore,
  getSupplierPerformance,
  getSupplierRankings
};
