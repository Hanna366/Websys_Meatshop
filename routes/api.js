const express = require('express');
const { body, query } = require('express-validator');
const router = express.Router();

const apiController = require('../controllers/apiController');
const { authenticateToken, requirePermission, requireFeature } = require('../middleware/auth');

// Validation rules
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
    .withMessage('Unit type must be lb, kg, or piece')
];

const getInventoryBatchesValidation = [
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('product_id')
    .optional()
    .isMongoId()
    .withMessage('Valid product ID is required'),
  query('status')
    .optional()
    .isIn(['active', 'expiring_soon', 'expired', 'depleted', 'quarantined', 'recalled'])
    .withMessage('Invalid status')
];

const createSaleValidation = [
  body('items')
    .isArray({ min: 1 })
    .withMessage('At least one item is required'),
  body('items.*.product_id')
    .isMongoId()
    .withMessage('Valid product ID is required for each item'),
  body('items.*.quantity.weight')
    .isFloat({ min: 0.01 })
    .withMessage('Weight must be greater than 0'),
  body('items.*.quantity.unit')
    .isIn(['lb', 'kg', 'piece'])
    .withMessage('Unit must be lb, kg, or piece'),
  body('payment.payment_method')
    .isIn(['cash', 'card', 'check', 'mobile_pay', 'store_credit'])
    .withMessage('Valid payment method is required'),
  body('customer_id')
    .optional()
    .isMongoId()
    .withMessage('Valid customer ID is required')
];

const getCustomersValidation = [
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('search')
    .optional()
    .trim()
    .isLength({ min: 1, max: 100 })
    .withMessage('Search query must be between 1 and 100 characters')
];

const createCustomerValidation = [
  body('personal_info.first_name')
    .trim()
    .notEmpty()
    .withMessage('First name is required'),
  body('personal_info.last_name')
    .trim()
    .notEmpty()
    .withMessage('Last name is required'),
  body('personal_info.phone')
    .trim()
    .notEmpty()
    .withMessage('Phone number is required'),
  body('personal_info.email')
    .optional()
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email is required'),
  body('customer_code')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Customer code cannot be empty')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// Apply API access feature check
router.use(requireFeature('api_access'));

// Apply API rate limiting to all routes
router.use(apiController.checkApiRateLimit);

// GET /api/v1/docs - Get API documentation
router.get('/docs', apiController.getApiDocs);

// GET /api/v1/usage - Get API usage statistics
router.get('/usage', apiController.getApiUsage);

// Products endpoints
router.get('/products', getProductsValidation, apiController.getApiProducts);
router.post('/products', 
  requirePermission('can_manage_inventory'),
  createProductValidation, 
  apiController.createApiProduct
);

// Inventory endpoints
router.get('/inventory/batches', getInventoryBatchesValidation, apiController.getApiInventoryBatches);

// Sales endpoints
router.post('/sales', 
  requirePermission('can_process_sales'),
  createSaleValidation, 
  apiController.createApiSale
);

// Customers endpoints
router.get('/customers', getCustomersValidation, apiController.getApiCustomers);
router.post('/customers', 
  requirePermission('can_manage_customers'),
  createCustomerValidation, 
  apiController.createApiCustomer
);

module.exports = router;
