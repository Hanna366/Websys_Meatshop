const express = require('express');
const { body, param, query } = require('express-validator');
const router = express.Router();

const productController = require('../controllers/productController');
const { authenticateToken, requirePermission, requireFeature, checkUsageLimit } = require('../middleware/auth');

// Validation rules
const createProductValidation = [
  body('product_code')
    .trim()
    .notEmpty()
    .withMessage('Product code is required'),
  body('name')
    .trim()
    .notEmpty()
    .withMessage('Product name is required'),
  body('category')
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Valid category is required'),
  body('pricing.price_per_unit')
    .isFloat({ min: 0 })
    .withMessage('Price per unit must be a positive number'),
  body('pricing.unit_type')
    .isIn(['lb', 'kg', 'piece'])
    .withMessage('Unit type must be lb, kg, or piece'),
  body('inventory.reorder_level')
    .optional()
    .isFloat({ min: 0 })
    .withMessage('Reorder level must be a positive number'),
  body('inventory.unit_of_measure')
    .isIn(['lb', 'kg', 'piece'])
    .withMessage('Unit of measure must be lb, kg, or piece')
];

const updateProductValidation = [
  body('product_code')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Product code cannot be empty'),
  body('name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Product name cannot be empty'),
  body('category')
    .optional()
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Valid category is required'),
  body('pricing.price_per_unit')
    .optional()
    .isFloat({ min: 0 })
    .withMessage('Price per unit must be a positive number'),
  body('pricing.unit_type')
    .optional()
    .isIn(['lb', 'kg', 'piece'])
    .withMessage('Unit type must be lb, kg, or piece'),
  body('inventory.reorder_level')
    .optional()
    .isFloat({ min: 0 })
    .withMessage('Reorder level must be a positive number'),
  body('status')
    .optional()
    .isIn(['active', 'inactive', 'discontinued'])
    .withMessage('Invalid status')
];

const getProductsValidation = [
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('category')
    .optional()
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Invalid category'),
  query('status')
    .optional()
    .isIn(['active', 'inactive', 'discontinued'])
    .withMessage('Invalid status'),
  query('search')
    .optional()
    .trim()
    .isLength({ min: 1, max: 100 })
    .withMessage('Search query must be between 1 and 100 characters')
];

const searchValidation = [
  query('q')
    .trim()
    .isLength({ min: 1, max: 100 })
    .withMessage('Search query must be between 1 and 100 characters'),
  query('category')
    .optional()
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Invalid category'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// Apply inventory management permission for all routes except GET
router.use(['POST', 'PUT', 'DELETE'], requirePermission('can_manage_inventory'));

// Apply feature check for inventory tracking
router.use(requireFeature('inventory_tracking'));

// GET /api/products - Get all products
router.get('/', getProductsValidation, productController.getProducts);

// GET /api/products/categories - Get product categories
router.get('/categories', productController.getCategories);

// GET /api/products/search - Search products
router.get('/search', searchValidation, productController.searchProducts);

// GET /api/products/low-stock - Get low stock products
router.get('/low-stock', productController.getLowStockProducts);

// GET /api/products/:product_id - Get single product
router.get('/:product_id', 
  param('product_id').isMongoId().withMessage('Valid product ID is required'),
  productController.getProduct
);

// POST /api/products - Create new product
router.post('/', 
  checkUsageLimit('products'),
  createProductValidation, 
  productController.createProduct
);

// PUT /api/products/:product_id - Update product
router.put('/:product_id', 
  param('product_id').isMongoId().withMessage('Valid product ID is required'),
  updateProductValidation, 
  productController.updateProduct
);

// DELETE /api/products/:product_id - Delete product
router.delete('/:product_id', 
  param('product_id').isMongoId().withMessage('Valid product ID is required'),
  productController.deleteProduct
);

module.exports = router;
