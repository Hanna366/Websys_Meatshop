const express = require('express');
const { body } = require('express-validator');
const router = express.Router();

const authController = require('../controllers/authController');
const { authenticateToken } = require('../middleware/auth');

// Validation rules
const registerValidation = [
  body('business_name')
    .trim()
    .notEmpty()
    .withMessage('Business name is required'),
  body('business_email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid business email is required'),
  body('business_phone')
    .notEmpty()
    .withMessage('Business phone is required'),
  body('business_address.street')
    .notEmpty()
    .withMessage('Street address is required'),
  body('business_address.city')
    .notEmpty()
    .withMessage('City is required'),
  body('business_address.state')
    .notEmpty()
    .withMessage('State is required'),
  body('business_address.zip_code')
    .notEmpty()
    .withMessage('Zip code is required'),
  body('first_name')
    .trim()
    .notEmpty()
    .withMessage('First name is required'),
  body('last_name')
    .trim()
    .notEmpty()
    .withMessage('Last name is required'),
  body('email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email is required'),
  body('password')
    .isLength({ min: 6 })
    .withMessage('Password must be at least 6 characters long'),
  body('phone')
    .notEmpty()
    .withMessage('Phone number is required')
];

const loginValidation = [
  body('email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email is required'),
  body('password')
    .notEmpty()
    .withMessage('Password is required')
];

const updateProfileValidation = [
  body('profile.first_name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('First name cannot be empty'),
  body('profile.last_name')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Last name cannot be empty'),
  body('profile.phone')
    .optional()
    .notEmpty()
    .withMessage('Phone cannot be empty'),
  body('preferences.language')
    .optional()
    .isIn(['en', 'es', 'fr'])
    .withMessage('Invalid language preference'),
  body('preferences.timezone')
    .optional()
    .notEmpty()
    .withMessage('Timezone cannot be empty'),
  body('preferences.theme')
    .optional()
    .isIn(['light', 'dark'])
    .withMessage('Invalid theme preference')
];

const changePasswordValidation = [
  body('current_password')
    .notEmpty()
    .withMessage('Current password is required'),
  body('new_password')
    .isLength({ min: 6 })
    .withMessage('New password must be at least 6 characters long')
];

// Public routes
router.post('/register', registerValidation, authController.register);
router.post('/login', loginValidation, authController.login);

// Protected routes
router.get('/profile', authenticateToken, authController.getProfile);
router.put('/profile', authenticateToken, updateProfileValidation, authController.updateProfile);
router.post('/refresh', authenticateToken, authController.refreshToken);
router.post('/logout', authenticateToken, authController.logout);
router.put('/change-password', authenticateToken, changePasswordValidation, authController.changePassword);

module.exports = router;
