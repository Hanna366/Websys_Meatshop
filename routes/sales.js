const express = require('express');
const { body, param, query } = require('express-validator');
const router = express.Router();

const salesController = require('../controllers/salesController');
const { authenticateToken, requirePermission, requireFeature } = require('../middleware/auth');

// Validation rules
const processSaleValidation = [
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
  body('payment.amount_paid')
    .isFloat({ min: 0 })
    .withMessage('Amount paid must be a positive number'),
  body('payment.discount_amount')
    .optional()
    .isFloat({ min: 0 })
    .withMessage('Discount amount must be a positive number'),
  body('customer_id')
    .optional()
    .isMongoId()
    .withMessage('Valid customer ID is required'),
  body('notes.customer_notes')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Customer notes must be less than 500 characters'),
  body('notes.internal_notes')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Internal notes must be less than 500 characters')
];

const voidSaleValidation = [
  body('void_reason')
    .trim()
    .notEmpty()
    .withMessage('Void reason is required')
    .isLength({ min: 5, max: 200 })
    .withMessage('Void reason must be between 5 and 200 characters')
];

const getSalesValidation = [
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('start_date')
    .optional()
    .isISO8601()
    .withMessage('Valid start date is required'),
  query('end_date')
    .optional()
    .isISO8601()
    .withMessage('Valid end date is required'),
  query('customer_id')
    .optional()
    .isMongoId()
    .withMessage('Valid customer ID is required'),
  query('cashier_id')
    .optional()
    .isMongoId()
    .withMessage('Valid cashier ID is required'),
  query('payment_method')
    .optional()
    .isIn(['cash', 'card', 'check', 'mobile_pay', 'store_credit'])
    .withMessage('Invalid payment method'),
  query('status')
    .optional()
    .isIn(['completed', 'pending', 'cancelled', 'refunded'])
    .withMessage('Invalid status')
];

const getSalesSummaryValidation = [
  query('start_date')
    .optional()
    .isISO8601()
    .withMessage('Valid start date is required'),
  query('end_date')
    .optional()
    .isISO8601()
    .withMessage('Valid end date is required')
];

const getDailyReportValidation = [
  query('date')
    .optional()
    .isISO8601()
    .withMessage('Valid date is required')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// GET /api/sales - Get sales history
router.get('/', getSalesValidation, salesController.getSales);

// GET /api/sales/summary - Get sales summary
router.get('/summary', getSalesSummaryValidation, salesController.getSalesSummary);

// GET /api/sales/daily-report - Get daily sales report
router.get('/daily-report', getDailyReportValidation, salesController.getDailySalesReport);

// GET /api/sales/:sale_id - Get single sale
router.get('/:sale_id', 
  param('sale_id').isMongoId().withMessage('Valid sale ID is required'),
  salesController.getSale
);

// POST /api/sales - Process new sale
router.post('/', 
  requireFeature('pos_system'),
  requirePermission('can_process_sales'),
  processSaleValidation, 
  salesController.processSale
);

// POST /api/sales/:sale_id/void - Void sale
router.post('/:sale_id/void', 
  requirePermission('can_process_sales'),
  param('sale_id').isMongoId().withMessage('Valid sale ID is required'),
  voidSaleValidation, 
  salesController.voidSale
);

module.exports = router;
