const express = require('express');
const { body, param } = require('express-validator');
const router = express.Router();

const { getTenantStats, setTenantStatus } = require('../utils/tenantUtils');
const { authenticateToken, requireRole } = require('../middleware/auth');

// GET /api/tenants/stats - Get tenant statistics
router.get('/stats', authenticateToken, async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;
    const stats = await getTenantStats(tenant_id);
    
    if (!stats) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    res.json({
      success: true,
      data: { stats }
    });
  } catch (error) {
    console.error('Get tenant stats error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get tenant statistics',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
});

// PUT /api/tenants/status - Update tenant status (owner only)
router.put('/status', 
  authenticateToken,
  requireRole('owner'),
  body('status')
    .isIn(['active', 'inactive', 'suspended'])
    .withMessage('Valid status is required'),
  body('reason')
    .optional()
    .trim()
    .isLength({ max: 500 })
    .withMessage('Reason must be less than 500 characters'),
  async (req, res) => {
    try {
      const { status, reason } = req.body;
      const tenant_id = req.user.tenant_id;

      const tenant = await setTenantStatus(tenant_id, status, reason);
      
      res.json({
        success: true,
        message: 'Tenant status updated successfully',
        data: { tenant }
      });
    } catch (error) {
      console.error('Update tenant status error:', error);
      res.status(500).json({
        success: false,
        message: 'Failed to update tenant status',
        error: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }
);

module.exports = router;
