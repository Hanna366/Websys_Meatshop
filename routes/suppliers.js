const express = require('express');
const { body, param, query } = require('express-validator');
const router = express.Router();

const supplierController = require('../controllers/supplierController');
const { authenticateToken, requirePermission, requireFeature } = require('../middleware/auth');

// Validation rules
const createSupplierValidation = [
  body('supplier_code')
    .trim()
    .notEmpty()
    .withMessage('Supplier code is required'),
  body('business_name')
    .trim()
    .notEmpty()
    .withMessage('Business name is required'),
  body('contact_info.primary_contact.name')
    .trim()
    .notEmpty()
    .withMessage('Primary contact name is required'),
  body('contact_info.primary_contact.phone')
    .trim()
    .notEmpty()
    .withMessage('Primary contact phone is required'),
  body('contact_info.primary_contact.email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid primary contact email is required'),
  body('address.street')
    .trim()
    .notEmpty()
    .withMessage('Street address is required'),
  body('address.city')
    .trim()
    .notEmpty()
    .withMessage('City is required'),
  body('address.state')
    .trim()
    .notEmpty()
    .withMessage('State is required'),
  body('address.zip_code')
    .trim()
    .notEmpty()
    .withMessage('Zip code is required'),
  body('payment_terms.payment_method')
    .isIn(['cash', 'check', 'wire', 'credit_card', 'net_30', 'net_60'])
    .withMessage('Valid payment method is required')
];

const updateSupplierValidation = [
  body('supplier_code')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Supplier code cannot be empty'),
  body('business_name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Business name cannot be empty'),
  body('contact_info.primary_contact.name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Primary contact name cannot be empty'),
  body('contact_info.primary_contact.email')
    .optional()
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid primary contact email is required'),
  body('status')
    .optional()
    .isIn(['active', 'inactive', 'suspended', 'under_review'])
    .withMessage('Invalid status'),
  body('preferences.preferred_supplier')
    .optional()
    .isBoolean()
    .withMessage('Preferred supplier must be a boolean')
];

const updateQualityScoreValidation = [
  body('quality_score')
    .isFloat({ min: 0, max: 100 })
    .withMessage('Quality score must be between 0 and 100'),
  body('inspection_notes')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Inspection notes must be less than 500 characters')
];

const getSuppliersValidation = [
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('status')
    .optional()
    .isIn(['active', 'inactive', 'suspended', 'under_review'])
    .withMessage('Invalid status'),
  query('preferred')
    .optional()
    .isBoolean()
    .withMessage('Preferred must be a boolean'),
  query('search')
    .optional()
    .trim()
    .isLength({ min: 1, max: 100 })
    .withMessage('Search query must be between 1 and 100 characters')
];

const getPerformanceValidation = [
  query('period')
    .optional()
    .isInt({ min: 7, max: 365 })
    .withMessage('Period must be between 7 and 365 days')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// Apply supplier management permission for all routes except GET
router.use(['POST', 'PUT', 'DELETE'], requirePermission('can_manage_suppliers'));

// Apply feature check for supplier management
router.use(requireFeature('supplier_management'));

// GET /api/suppliers - Get all suppliers
router.get('/', getSuppliersValidation, supplierController.getSuppliers);

// GET /api/suppliers/rankings - Get supplier rankings
router.get('/rankings', supplierController.getSupplierRankings);

// GET /api/suppliers/:supplier_id - Get single supplier
router.get('/:supplier_id', 
  param('supplier_id').isMongoId().withMessage('Valid supplier ID is required'),
  supplierController.getSupplier
);

// GET /api/suppliers/:supplier_id/performance - Get supplier performance metrics
router.get('/:supplier_id/performance', 
  param('supplier_id').isMongoId().withMessage('Valid supplier ID is required'),
  getPerformanceValidation,
  supplierController.getSupplierPerformance
);

// POST /api/suppliers - Create new supplier
router.post('/', createSupplierValidation, supplierController.createSupplier);

// PUT /api/suppliers/:supplier_id - Update supplier
router.put('/:supplier_id', 
  param('supplier_id').isMongoId().withMessage('Valid supplier ID is required'),
  updateSupplierValidation, 
  supplierController.updateSupplier
);

// PUT /api/suppliers/:supplier_id/quality-score - Update supplier quality score
router.put('/:supplier_id/quality-score', 
  param('supplier_id').isMongoId().withMessage('Valid supplier ID is required'),
  updateQualityScoreValidation, 
  supplierController.updateQualityScore
);

// DELETE /api/suppliers/:supplier_id - Delete supplier
router.delete('/:supplier_id', 
  param('supplier_id').isMongoId().withMessage('Valid supplier ID is required'),
  supplierController.deleteSupplier
);

module.exports = router;
