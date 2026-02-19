const express = require('express');
const { body, query } = require('express-validator');
const router = express.Router();

const subscriptionController = require('../controllers/subscriptionController');
const { authenticateToken, requirePermission } = require('../middleware/auth');

// Validation rules
const createSubscriptionValidation = [
  body('plan_id')
    .isIn(['basic', 'standard', 'premium'])
    .withMessage('Valid plan ID is required'),
  body('payment_method_id')
    .notEmpty()
    .withMessage('Payment method ID is required')
];

const updateSubscriptionValidation = [
  body('new_plan_id')
    .isIn(['basic', 'standard', 'premium'])
    .withMessage('Valid plan ID is required')
];

const cancelSubscriptionValidation = [
  body('cancel_at_period_end')
    .optional()
    .isBoolean()
    .withMessage('Cancel at period end must be a boolean'),
  body('reason')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Reason must be less than 500 characters')
];

const updatePaymentMethodValidation = [
  body('payment_method_id')
    .notEmpty()
    .withMessage('Payment method ID is required')
];

// Apply authentication middleware to all routes
router.use(authenticateToken);

// GET /api/subscriptions/current - Get current subscription
router.get('/current', subscriptionController.getSubscription);

// GET /api/subscriptions/plans - Get available plans
router.get('/plans', subscriptionController.getPlans);

// GET /api/subscriptions/usage - Get usage statistics
router.get('/usage', subscriptionController.getUsageStats);

// GET /api/subscriptions/billing - Get billing history
router.get('/billing', subscriptionController.getBillingHistory);

// POST /api/subscriptions - Create new subscription
router.post('/', createSubscriptionValidation, subscriptionController.createSubscription);

// PUT /api/subscriptions/plan - Update subscription plan
router.put('/plan', updateSubscriptionValidation, subscriptionController.updateSubscriptionPlan);

// PUT /api/subscriptions/payment-method - Update payment method
router.put('/payment-method', updatePaymentMethodValidation, subscriptionController.updatePaymentMethod);

// POST /api/subscriptions/cancel - Cancel subscription
router.post('/cancel', cancelSubscriptionValidation, subscriptionController.cancelSubscription);

module.exports = router;
