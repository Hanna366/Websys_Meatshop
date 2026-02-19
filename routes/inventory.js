const express = require('express');
const { body, param, query } = require('express-validator');
const router = express.Router();

const inventoryController = require('../controllers/inventoryController');
const { authenticateToken, requirePermission, requireFeature } = require('../middleware/auth');

// Validation rules
const addBatchValidation = [
  body('product_id')
    .isMongoId()
    .withMessage('Valid product ID is required'),
  body('batch_number')
    .trim()
    .notEmpty()
    .withMessage('Batch number is required'),
  body('supplier_id')
    .isMongoId()
    .withMessage('Valid supplier ID is required'),
  body('quantity.initial_quantity')
    .isFloat({ min: 0 })
    .withMessage('Initial quantity must be a positive number'),
  body('quantity.unit')
    .isIn(['lb', 'kg', 'piece'])
    .withMessage('Unit must be lb, kg, or piece'),
  body('cost.unit_cost')
    .isFloat({ min: 0 })
    .withMessage('Unit cost must be a positive number'),
  body('dates.expiry_date')
    .isISO8601()
    .withMessage('Valid expiry date is required'),
  body('dates.received_date')
    .optional()
    .isISO8601()
    .withMessage('Valid received date is required')
];

const updateBatchValidation = [
  body('quantity.current_quantity')
    .optional()
    .isFloat({ min: 0 })
    .withMessage('Current quantity must be a positive number'),
  body('cost.unit_cost')
    .optional()
    .isFloat({ min: 0 })
    .withMessage('Unit cost must be a positive number'),
  body('dates.expiry_date')
    .optional()
    .isISO8601()
    .withMessage('Valid expiry date is required'),
  body('storage.location')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Storage location cannot be empty')
];

const recordWasteValidation = [
  body('waste_quantity')
    .isFloat({ min: 0 })
    .withMessage('Waste quantity must be a positive number'),
  body('waste_reason')
    .isIn(['expiry', 'spoilage', 'damage', 'contamination', 'theft', 'other'])
    .withMessage('Valid waste reason is required'),
  body('waste_notes')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Waste notes must be less than 500 characters')
];

const getInventoryValidation = [
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
  query('low_stock')
    .optional()
    .isBoolean()
    .withMessage('Low stock must be a boolean')
];

const getAlertsValidation = [
  query('alert_type')
    .optional()
    .isIn(['low_stock', 'expiry', 'quality'])
    .withMessage('Invalid alert type')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// Apply inventory management permission to all routes
router.use(requirePermission('can_manage_inventory'));

// Apply feature check for inventory tracking
router.use(requireFeature('inventory_tracking'));

// GET /api/inventory - Get all inventory items
router.get('/', getInventoryValidation, inventoryController.getInventory);

// GET /api/inventory/stats - Get inventory statistics
router.get('/stats', inventoryController.getInventoryStats);

// GET /api/inventory/alerts - Get inventory alerts
router.get('/alerts', getAlertsValidation, inventoryController.getInventoryAlerts);

// GET /api/inventory/product/:product_id/batches - Get batches for a specific product
router.get('/product/:product_id/batches', 
  param('product_id').isMongoId().withMessage('Valid product ID is required'),
  inventoryController.getProductBatches
);

// POST /api/inventory/batch - Add new inventory batch
router.post('/batch', addBatchValidation, inventoryController.addInventoryBatch);

// PUT /api/inventory/batch/:batch_id - Update inventory batch
router.put('/batch/:batch_id', 
  param('batch_id').isMongoId().withMessage('Valid batch ID is required'),
  updateBatchValidation, 
  inventoryController.updateInventoryBatch
);

// POST /api/inventory/batch/:batch_id/waste - Record waste for batch
router.post('/batch/:batch_id/waste', 
  param('batch_id').isMongoId().withMessage('Valid batch ID is required'),
  recordWasteValidation, 
  inventoryController.recordWaste
);

module.exports = router;
