const express = require('express');
const { body, param, query } = require('express-validator');
const router = express.Router();

const User = require('../models/User');
const { updateTenantUsage } = require('../utils/tenantUtils');
const { authenticateToken, requirePermission, checkUsageLimit } = require('../middleware/auth');

// GET /api/users - Get all users for tenant
router.get('/', 
  authenticateToken,
  requirePermission('can_manage_users'),
  query('page')
    .optional()
    .isInt({ min: 1 })
    .withMessage('Page must be a positive integer'),
  query('limit')
    .optional()
    .isInt({ min: 1, max: 100 })
    .withMessage('Limit must be between 1 and 100'),
  query('role')
    .optional()
    .isIn(['owner', 'manager', 'cashier', 'inventory_staff'])
    .withMessage('Invalid role'),
  query('status')
    .optional()
    .isIn(['active', 'inactive', 'suspended'])
    .withMessage('Invalid status'),
  async (req, res) => {
    try {
      const { page = 1, limit = 50, role, status } = req.query;
      const tenant_id = req.user.tenant_id;

      // Build query
      const query = { tenant_id };
      if (role) query.role = role;
      if (status) query.status = status;

      const users = await User.find(query)
        .select('-password')
        .sort({ created_at: -1 })
        .limit(limit * 1)
        .skip((page - 1) * limit);

      const total = await User.countDocuments(query);

      res.json({
        success: true,
        data: {
          users,
          pagination: {
            current_page: parseInt(page),
            total_pages: Math.ceil(total / limit),
            total_items: total,
            items_per_page: parseInt(limit)
          }
        }
      });
    } catch (error) {
      console.error('Get users error:', error);
      res.status(500).json({
        success: false,
        message: 'Failed to get users',
        error: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }
);

// GET /api/users/:user_id - Get single user
router.get('/:user_id', 
  authenticateToken,
  requirePermission('can_manage_users'),
  param('user_id').isMongoId().withMessage('Valid user ID is required'),
  async (req, res) => {
    try {
      const { user_id } = req.params;
      const tenant_id = req.user.tenant_id;

      const user = await User.findOne({ tenant_id, _id: user_id }).select('-password');
      
      if (!user) {
        return res.status(404).json({
          success: false,
          message: 'User not found'
        });
      }

      res.json({
        success: true,
        data: { user }
      });
    } catch (error) {
      console.error('Get user error:', error);
      res.status(500).json({
        success: false,
        message: 'Failed to get user',
        error: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }
);

// POST /api/users - Create new user
router.post('/', 
  authenticateToken,
  requirePermission('can_manage_users'),
  checkUsageLimit('users'),
  body('username')
    .trim()
    .notEmpty()
    .withMessage('Username is required'),
  body('email')
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email is required'),
  body('password')
    .isLength({ min: 6 })
    .withMessage('Password must be at least 6 characters long'),
  body('role')
    .isIn(['owner', 'manager', 'cashier', 'inventory_staff'])
    .withMessage('Valid role is required'),
  body('profile.first_name')
    .trim()
    .notEmpty()
    .withMessage('First name is required'),
  body('profile.last_name')
    .trim()
    .notEmpty()
    .withMessage('Last name is required'),
  body('profile.phone')
    .trim()
    .notEmpty()
    .withMessage('Phone number is required'),
  async (req, res) => {
    try {
      const tenant_id = req.user.tenant_id;

      // Check if email already exists
      const existingUser = await User.findOne({ email: req.body.email });
      if (existingUser) {
        return res.status(400).json({
          success: false,
          message: 'Email already exists'
        });
      }

      // Create user
      const user = new User({
        tenant_id,
        ...req.body
      });

      await user.save();

      // Update tenant usage
      await updateTenantUsage(tenant_id, 'users', 1);

      res.status(201).json({
        success: true,
        message: 'User created successfully',
        data: { 
          user: user.toObject({ 
            transform: (doc, ret) => { 
              delete ret.password; 
              return ret; 
            } 
          })
        }
      });
    } catch (error) {
      console.error('Create user error:', error);
      res.status(500).json({
        success: false,
        message: 'Failed to create user',
        error: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }
);

// PUT /api/users/:user_id - Update user
router.put('/:user_id', 
  authenticateToken,
  requirePermission('can_manage_users'),
  param('user_id').isMongoId().withMessage('Valid user ID is required'),
  body('username')
    .optional()
    .trim()
    .notEmpty()
    .withMessage('Username cannot be empty'),
  body('email')
    .optional()
    .isEmail()
    .normalizeEmail()
    .withMessage('Valid email is required'),
  body('role')
    .optional()
    .isIn(['owner', 'manager', 'cashier', 'inventory_staff'])
    .withMessage('Valid role is required'),
  body('status')
    .optional()
    .isIn(['active', 'inactive', 'suspended'])
    .withMessage('Invalid status'),
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
  async (req, res) => {
    try {
      const { user_id } = req.params;
      const tenant_id = req.user.tenant_id;
      const updates = req.body;

      const user = await User.findOne({ tenant_id, _id: user_id });
      if (!user) {
        return res.status(404).json({
          success: false,
          message: 'User not found'
        });
      }

      // Check if email is being changed and if it conflicts
      if (updates.email && updates.email !== user.email) {
        const existingUser = await User.findOne({
          email: updates.email,
          _id: { $ne: user_id }
        });
        if (existingUser) {
          return res.status(400).json({
            success: false,
            message: 'Email already exists'
          });
        }
      }

      // Update user
      Object.assign(user, updates);
      user.updated_by = req.user._id;
      await user.save();

      res.json({
        success: true,
        message: 'User updated successfully',
        data: { 
          user: user.toObject({ 
            transform: (doc, ret) => { 
              delete ret.password; 
              return ret; 
            } 
          })
        }
      });
    } catch (error) {
      console.error('Update user error:', error);
      res.status(500).json({
        success: false,
        message: 'Failed to update user',
        error: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }
);

// DELETE /api/users/:user_id - Delete user
router.delete('/:user_id', 
  authenticateToken,
  requirePermission('can_manage_users'),
  param('user_id').isMongoId().withMessage('Valid user ID is required'),
  async (req, res) => {
    try {
      const { user_id } = req.params;
      const tenant_id = req.user.tenant_id;

      const user = await User.findOne({ tenant_id, _id: user_id });
      if (!user) {
        return res.status(404).json({
          success: false,
          message: 'User not found'
        });
      }

      // Prevent deletion of the last owner
      if (user.role === 'owner') {
        const ownerCount = await User.countDocuments({ tenant_id, role: 'owner' });
        if (ownerCount <= 1) {
          return res.status(400).json({
            success: false,
            message: 'Cannot delete the last owner account'
          });
        }
      }

      // Soft delete by marking as inactive
      user.status = 'inactive';
      user.updated_by = req.user._id;
      await user.save();

      // Update tenant usage
      await updateTenantUsage(tenant_id, 'users', -1);

      res.json({
        success: true,
        message: 'User deleted successfully'
      });
    } catch (error) {
      console.error('Delete user error:', error);
      res.status(500).json({
        success: false,
        message: 'Failed to delete user',
        error: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }
);

module.exports = router;
