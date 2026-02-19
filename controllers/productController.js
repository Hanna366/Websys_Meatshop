const Product = require('../models/Product');
const { validationResult } = require('express-validator');
const { updateTenantUsage, checkUsageLimits } = require('../utils/tenantUtils');

// Get all products for a tenant
const getProducts = async (req, res) => {
  try {
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
      .populate('supplier_info.primary_supplier_id', 'business_name supplier_code')
      .sort({ name: 1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Product.countDocuments(query);

    res.json({
      success: true,
      data: {
        products,
        pagination: {
          current_page: page,
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: limit
        }
      }
    });
  } catch (error) {
    console.error('Get products error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get products',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get single product
const getProduct = async (req, res) => {
  try {
    const { product_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const product = await Product.findOne({ tenant_id, _id: product_id })
      .populate('supplier_info.primary_supplier_id', 'business_name supplier_code')
      .populate('supplier_info.backup_supplier_ids', 'business_name supplier_code')
      .populate('created_by', 'username')
      .populate('updated_by', 'username');

    if (!product) {
      return res.status(404).json({
        success: false,
        message: 'Product not found'
      });
    }

    res.json({
      success: true,
      data: { product }
    });
  } catch (error) {
    console.error('Get product error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get product',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Create new product
const createProduct = async (req, res) => {
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

    // Check if tenant has reached product limit
    const usageCheck = await checkUsageLimits(tenant_id, 'products');
    if (!usageCheck.allowed) {
      return res.status(429).json({
        success: false,
        message: `Product limit reached (${usageCheck.limit})`
      });
    }

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

    // Update tenant usage
    await updateTenantUsage(tenant_id, 'products', 1);

    res.status(201).json({
      success: true,
      message: 'Product created successfully',
      data: { product }
    });
  } catch (error) {
    console.error('Create product error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create product',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update product
const updateProduct = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { product_id } = req.params;
    const tenant_id = req.user.tenant_id;
    const updates = req.body;

    const product = await Product.findOne({ tenant_id, _id: product_id });
    if (!product) {
      return res.status(404).json({
        success: false,
        message: 'Product not found'
      });
    }

    // Check if product code is being changed and if it conflicts
    if (updates.product_code && updates.product_code !== product.product_code) {
      const existingProduct = await Product.findOne({
        tenant_id,
        product_code: updates.product_code,
        _id: { $ne: product_id }
      });
      if (existingProduct) {
        return res.status(400).json({
          success: false,
          message: 'Product code already exists'
        });
      }
    }

    // Update product
    Object.assign(product, updates);
    product.updated_by = req.user._id;
    await product.save();

    res.json({
      success: true,
      message: 'Product updated successfully',
      data: { product }
    });
  } catch (error) {
    console.error('Update product error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update product',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Delete product
const deleteProduct = async (req, res) => {
  try {
    const { product_id } = req.params;
    const tenant_id = req.user.tenant_id;

    const product = await Product.findOne({ tenant_id, _id: product_id });
    if (!product) {
      return res.status(404).json({
        success: false,
        message: 'Product not found'
      });
    }

    // Check if product has inventory
    if (product.inventory.current_stock > 0) {
      return res.status(400).json({
        success: false,
        message: 'Cannot delete product with existing inventory'
      });
    }

    // Soft delete by marking as discontinued
    product.status = 'discontinued';
    product.updated_by = req.user._id;
    await product.save();

    // Update tenant usage
    await updateTenantUsage(tenant_id, 'products', -1);

    res.json({
      success: true,
      message: 'Product deleted successfully'
    });
  } catch (error) {
    console.error('Delete product error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to delete product',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get product categories
const getCategories = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const categories = await Product.distinct('category', { tenant_id, status: 'active' });

    const categoryStats = await Promise.all(
      categories.map(async (category) => {
        const count = await Product.countDocuments({ tenant_id, category, status: 'active' });
        const totalStock = await Product.aggregate([
          { $match: { tenant_id, category, status: 'active' } },
          { $group: { _id: null, total: { $sum: '$inventory.current_stock' } } }
        ]);

        return {
          name: category,
          product_count: count,
          total_stock: totalStock[0]?.total || 0
        };
      })
    );

    res.json({
      success: true,
      data: { categories: categoryStats }
    });
  } catch (error) {
    console.error('Get categories error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get categories',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Search products
const searchProducts = async (req, res) => {
  try {
    const { q, category, limit = 20 } = req.query;
    const tenant_id = req.user.tenant_id;

    if (!q) {
      return res.status(400).json({
        success: false,
        message: 'Search query is required'
      });
    }

    const query = {
      tenant_id,
      status: 'active',
      $or: [
        { name: { $regex: q, $options: 'i' } },
        { product_code: { $regex: q, $options: 'i' } },
        { description: { $regex: q, $options: 'i' } },
        { tags: { $in: [new RegExp(q, 'i')] } }
      ]
    };

    if (category) {
      query.category = category;
    }

    const products = await Product.find(query)
      .populate('supplier_info.primary_supplier_id', 'business_name')
      .limit(parseInt(limit))
      .sort({ name: 1 });

    res.json({
      success: true,
      data: { products }
    });
  } catch (error) {
    console.error('Search products error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to search products',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get low stock products
const getLowStockProducts = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const products = await Product.find({
      tenant_id,
      status: 'active',
      '$expr': { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] }
    })
    .populate('supplier_info.primary_supplier_id', 'business_name supplier_code')
    .sort({ 'inventory.current_stock': 1 });

    res.json({
      success: true,
      data: { products }
    });
  } catch (error) {
    console.error('Get low stock products error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get low stock products',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getProducts,
  getProduct,
  createProduct,
  updateProduct,
  deleteProduct,
  getCategories,
  searchProducts,
  getLowStockProducts
};
