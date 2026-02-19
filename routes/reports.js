const express = require('express');
const { query } = require('express-validator');
const router = express.Router();

const reportsController = require('../controllers/reportsController');
const { authenticateToken, requirePermission, requireFeature } = require('../middleware/auth');

// Validation rules
const getDashboardValidation = [
  query('period')
    .optional()
    .isInt({ min: 1, max: 365 })
    .withMessage('Period must be between 1 and 365 days')
];

const getSalesReportValidation = [
  query('start_date')
    .optional()
    .isISO8601()
    .withMessage('Valid start date is required'),
  query('end_date')
    .optional()
    .isISO8601()
    .withMessage('Valid end date is required'),
  query('group_by')
    .optional()
    .isIn(['hour', 'day', 'week', 'month'])
    .withMessage('Group by must be hour, day, week, or month'),
  query('customer_id')
    .optional()
    .isMongoId()
    .withMessage('Valid customer ID is required'),
  query('product_id')
    .optional()
    .isMongoId()
    .withMessage('Valid product ID is required'),
  query('category')
    .optional()
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Invalid category')
];

const getInventoryReportValidation = [
  query('category')
    .optional()
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Invalid category'),
  query('status')
    .optional()
    .isIn(['active', 'inactive', 'discontinued'])
    .withMessage('Invalid status'),
  query('include_valuation')
    .optional()
    .isBoolean()
    .withMessage('Include valuation must be a boolean'),
  query('include_movements')
    .optional()
    .isBoolean()
    .withMessage('Include movements must be a boolean')
];

const getCustomerReportValidation = [
  query('start_date')
    .optional()
    .isISO8601()
    .withMessage('Valid start date is required'),
  query('end_date')
    .optional()
    .isISO8601()
    .withMessage('Valid end date is required'),
  query('loyalty_tier')
    .optional()
    .isIn(['bronze', 'silver', 'gold', 'platinum'])
    .withMessage('Invalid loyalty tier'),
  query('group_by')
    .optional()
    .isIn(['acquisition', 'spending', 'frequency'])
    .withMessage('Group by must be acquisition, spending, or frequency')
];

const getSupplierReportValidation = [
  query('start_date')
    .optional()
    .isISO8601()
    .withMessage('Valid start date is required'),
  query('end_date')
    .optional()
    .isISO8601()
    .withMessage('Valid end date is required'),
  query('category')
    .optional()
    .isIn(['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other'])
    .withMessage('Invalid category'),
  query('include_quality')
    .optional()
    .isBoolean()
    .withMessage('Include quality must be a boolean')
];

const exportReportValidation = [
  query('report_type')
    .isIn(['sales', 'inventory', 'customers', 'suppliers'])
    .withMessage('Valid report type is required'),
  query('format')
    .optional()
    .isIn(['csv', 'json'])
    .withMessage('Format must be csv or json')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// Apply view reports permission
router.use(requirePermission('can_view_reports'));

// Apply feature check for reporting
router.use(requireFeature('basic_reporting'));

// GET /api/reports/dashboard - Get dashboard overview
router.get('/dashboard', getDashboardValidation, reportsController.getDashboardOverview);

// GET /api/reports/sales - Get sales report
router.get('/sales', getSalesReportValidation, reportsController.getSalesReport);

// GET /api/reports/inventory - Get inventory report
router.get('/inventory', getInventoryReportValidation, reportsController.getInventoryReport);

// GET /api/reports/customers - Get customer report
router.get('/customers', getCustomerReportValidation, reportsController.getCustomerReport);

// GET /api/reports/suppliers - Get supplier report
router.get('/suppliers', getSupplierReportValidation, reportsController.getSupplierReport);

// GET /api/reports/export - Export report data
router.get('/export', 
  requireFeature('data_export'),
  exportReportValidation, 
  reportsController.exportReport
);

module.exports = router;
