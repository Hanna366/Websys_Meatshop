const express = require('express');
const { body, param, query } = require('express-validator');
const router = express.Router();

const customerController = require('../controllers/customerController');
const { authenticateToken, requirePermission, requireFeature } = require('../middleware/auth');

// Validation rules
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
    .withMessage('Customer code cannot be empty'),
  body('address.street')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Street address cannot be empty'),
  body('address.city')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('City cannot be empty'),
  body('address.state')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('State cannot be empty'),
  body('address.zip_code')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Zip code cannot be empty')
];

const updateCustomerValidation = [
  body('personal_info.first_name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('First name cannot be empty'),
  body('personal_info.last_name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Last name cannot be empty'),
  body('personal_info.email')
    .optional()
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email is required'),
  body('status')
    .optional()
    .isIn(['active', 'inactive', 'suspended', 'blacklisted'])
    .withMessage('Invalid status'),
  body('loyalty.is_member')
    .optional()
    .isBoolean()
    .withMessage('Loyalty member status must be a boolean')
];

const loyaltyPointsValidation = [
  body('points')
    .isInt({ min: 1 })
    .withMessage('Points must be a positive integer'),
  body('reason')
    .optional()
    .trim()
    .isLength({ max: 200 })
    .withMessage('Reason must be less than 200 characters')
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
  query('status')
    .optional()
    .isIn(['active', 'inactive', 'suspended', 'blacklisted'])
    .withMessage('Invalid status'),
  query('loyalty_member')
    .optional()
    .isBoolean()
    .withMessage('Loyalty member must be a boolean'),
  query('search')
    .optional()
    .trim()
    .isLength({ min: 1, max: 100 })
    .withMessage('Search query must be between 1 and 100 characters')
];

const getPurchaseHistoryValidation = [
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
    .withMessage('Valid end date is required')
];

const getAnalyticsValidation = [
  query('period')
    .optional()
    .isInt({ min: 7, max: 365 })
    .withMessage('Period must be between 7 and 365 days')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// Apply customer management permission for all routes except GET
router.use(['POST', 'PUT', 'DELETE'], requirePermission('can_manage_customers'));

// Apply feature check for customer management
router.use(requireFeature('customer_management'));

// GET /api/customers - Get all customers
router.get('/', getCustomersValidation, customerController.getCustomers);

// GET /api/customers/:customer_id - Get single customer
router.get('/:customer_id', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  customerController.getCustomer
);

// GET /api/customers/:customer_id/purchase-history - Get customer purchase history
router.get('/:customer_id/purchase-history', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  getPurchaseHistoryValidation,
  customerController.getPurchaseHistory
);

// GET /api/customers/:customer_id/analytics - Get customer analytics
router.get('/:customer_id/analytics', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  getAnalyticsValidation,
  customerController.getCustomerAnalytics
);

// POST /api/customers - Create new customer
router.post('/', createCustomerValidation, customerController.createCustomer);

// PUT /api/customers/:customer_id - Update customer
router.put('/:customer_id', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  updateCustomerValidation, 
  customerController.updateCustomer
);

// POST /api/customers/:customer_id/loyalty/add - Add loyalty points
router.post('/:customer_id/loyalty/add', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  loyaltyPointsValidation, 
  customerController.addLoyaltyPoints
);

// POST /api/customers/:customer_id/loyalty/redeem - Redeem loyalty points
router.post('/:customer_id/loyalty/redeem', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  loyaltyPointsValidation, 
  customerController.redeemLoyaltyPoints
);

// DELETE /api/customers/:customer_id - Delete customer
router.delete('/:customer_id', 
  param('customer_id').isMongoId().withMessage('Valid customer ID is required'),
  customerController.deleteCustomer
);

module.exports = router;
