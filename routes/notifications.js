const express = require('express');
const { body, query } = require('express-validator');
const router = express.Router();

const notificationController = require('../controllers/notificationController');
const { authenticateToken, requirePermission } = require('../middleware/auth');

// Validation rules
const sendLowStockValidation = [
  body('product_id')
    .isMongoId()
    .withMessage('Valid product ID is required'),
  body('recipient_emails')
    .optional()
    .isArray()
    .withMessage('Recipient emails must be an array'),
  body('recipient_emails.*')
    .optional()
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email addresses are required'),
  body('recipient_phones')
    .optional()
    .isArray()
    .withMessage('Recipient phones must be an array'),
  body('recipient_phones.*')
    .optional()
    .isMobilePhone()
    .withMessage('Valid phone numbers are required')
];

const sendExpiryValidation = [
  body('batch_id')
    .isMongoId()
    .withMessage('Valid batch ID is required'),
  body('recipient_emails')
    .optional()
    .isArray()
    .withMessage('Recipient emails must be an array'),
  body('recipient_emails.*')
    .optional()
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email addresses are required'),
  body('recipient_phones')
    .optional()
    .isArray()
    .withMessage('Recipient phones must be an array'),
  body('recipient_phones.*')
    .optional()
    .isMobilePhone()
    .withMessage('Valid phone numbers are required')
];

const sendCustomerValidation = [
  body('customer_id')
    .isMongoId()
    .withMessage('Valid customer ID is required'),
  body('message_type')
    .isIn(['promotion', 'loyalty_points', 'custom'])
    .withMessage('Valid message type is required'),
  body('custom_message')
    .optional()
    .trim()
    .isLength({ min: 1, max: 1000 })
    .withMessage('Custom message must be between 1 and 1000 characters')
];

const updateSettingsValidation = [
  body('enable_email_notifications')
    .optional()
    .isBoolean()
    .withMessage('Enable email notifications must be a boolean'),
  body('enable_sms_notifications')
    .optional()
    .isBoolean()
    .withMessage('Enable SMS notifications must be a boolean'),
  body('low_stock_threshold')
    .optional()
    .isInt({ min: 0 })
    .withMessage('Low stock threshold must be a non-negative integer'),
  body('expiry_warning_days')
    .optional()
    .isInt({ min: 1, max: 30 })
    .withMessage('Expiry warning days must be between 1 and 30')
];

const getNotificationsValidation = [
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('type')
    .optional()
    .isIn(['all', 'low_stock', 'expiry', 'quality'])
    .withMessage('Invalid notification type'),
  query('status')
    .optional()
    .isIn(['all', 'read', 'unread'])
    .withMessage('Invalid status')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// GET /api/notifications - Get notifications
router.get('/', getNotificationsValidation, notificationController.getNotifications);

// GET /api/notifications/settings - Get notification settings
router.get('/settings', notificationController.getNotificationSettings);

// PUT /api/notifications/settings - Update notification settings
router.put('/settings', 
  requirePermission('can_manage_users'), // Only owners can change settings
  updateSettingsValidation, 
  notificationController.updateNotificationSettings
);

// POST /api/notifications/low-stock - Send low stock notification
router.post('/low-stock', 
  requirePermission('can_manage_inventory'),
  sendLowStockValidation, 
  notificationController.sendLowStockNotification
);

// POST /api/notifications/expiry - Send expiry notification
router.post('/expiry', 
  requirePermission('can_manage_inventory'),
  sendExpiryValidation, 
  notificationController.sendExpiryNotification
);

// POST /api/notifications/customer - Send customer notification
router.post('/customer', 
  requirePermission('can_manage_customers'),
  sendCustomerValidation, 
  notificationController.sendCustomerNotification
);

module.exports = router;
