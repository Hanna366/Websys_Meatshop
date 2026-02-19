const nodemailer = require('nodemailer');
const twilio = require('twilio');
const Product = require('../models/Product');
const InventoryBatch = require('../models/InventoryBatch');
const Customer = require('../models/Customer');
const Tenant = require('../models/Tenant');
const { validationResult } = require('express-validator');

// Initialize email transporter
const createEmailTransporter = () => {
  return nodemailer.createTransporter({
    host: process.env.EMAIL_HOST,
    port: process.env.EMAIL_PORT,
    secure: false, // true for 465, false for other ports
    auth: {
      user: process.env.EMAIL_USER,
      pass: process.env.EMAIL_PASS
    }
  });
};

// Initialize Twilio client
const createTwilioClient = () => {
  if (process.env.TWILIO_ACCOUNT_SID && process.env.TWILIO_AUTH_TOKEN) {
    return twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);
  }
  return null;
};

// Send email notification
const sendEmailNotification = async (tenant_id, recipient, subject, htmlContent, textContent) => {
  try {
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant || !tenant.settings.enable_email_notifications) {
      return { success: false, message: 'Email notifications disabled' };
    }

    const transporter = createEmailTransporter();
    
    const mailOptions = {
      from: `"${tenant.business_name}" <${process.env.EMAIL_USER}>`,
      to: recipient,
      subject: subject,
      text: textContent,
      html: htmlContent
    };

    const result = await transporter.sendMail(mailOptions);
    return { success: true, messageId: result.messageId };
  } catch (error) {
    console.error('Send email error:', error);
    return { success: false, error: error.message };
  }
};

// Send SMS notification
const sendSMSNotification = async (tenant_id, phoneNumber, message) => {
  try {
    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant || !tenant.settings.enable_sms_notifications) {
      return { success: false, message: 'SMS notifications disabled' };
    }

    const twilioClient = createTwilioClient();
    if (!twilioClient) {
      return { success: false, message: 'SMS service not configured' };
    }

    const result = await twilioClient.messages.create({
      body: message,
      from: process.env.TWILIO_PHONE_NUMBER,
      to: phoneNumber
    });

    return { success: true, messageId: result.sid };
  } catch (error) {
    console.error('Send SMS error:', error);
    return { success: false, error: error.message };
  }
};

// Get notifications
const getNotifications = async (req, res) => {
  try {
    const { page = 1, limit = 50, type, status } = req.query;
    const tenant_id = req.user.tenant_id;

    // For now, return real-time alerts from inventory
    // In a production system, you'd have a dedicated notifications collection
    const alerts = await getInventoryAlerts(tenant_id);

    // Filter by type if specified
    let filteredAlerts = alerts;
    if (type && type !== 'all') {
      filteredAlerts = alerts.filter(alert => alert.type === type);
    }

    // Pagination
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + parseInt(limit);
    const paginatedAlerts = filteredAlerts.slice(startIndex, endIndex);

    res.json({
      success: true,
      data: {
        notifications: paginatedAlerts,
        pagination: {
          current_page: parseInt(page),
          total_pages: Math.ceil(filteredAlerts.length / limit),
          total_items: filteredAlerts.length,
          items_per_page: parseInt(limit)
        }
      }
    });
  } catch (error) {
    console.error('Get notifications error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get notifications',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get inventory alerts (helper function)
const getInventoryAlerts = async (tenant_id) => {
  const alerts = [];

  // Low stock alerts
  const lowStockProducts = await Product.find({
    tenant_id,
    status: 'active',
    '$expr': { '$lte': ['$inventory.current_stock', '$inventory.reorder_level'] }
  }).populate('supplier_info.primary_supplier_id', 'business_name');

  lowStockProducts.forEach(product => {
    alerts.push({
      id: `low_stock_${product._id}`,
      type: 'low_stock',
      severity: 'medium',
      title: 'Low Stock Alert',
      message: `${product.name} is running low on stock (${product.inventory.current_stock} ${product.inventory.unit_of_measure} remaining)`,
      product_id: product._id,
      product_name: product.name,
      current_stock: product.inventory.current_stock,
      reorder_level: product.inventory.reorder_level,
      supplier: product.supplier_info.primary_supplier_id?.business_name || 'No supplier',
      created_at: new Date(),
      actions: [
        { type: 'view_product', label: 'View Product' },
        { type: 'create_purchase_order', label: 'Create Purchase Order' }
      ]
    });
  });

  // Expiry alerts
  const expiringBatches = await InventoryBatch.find({
    tenant_id,
    status: { $in: ['active', 'expiring_soon'] },
    'dates.expiry_date': { $lte: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) }
  })
  .populate('product_id', 'name product_code')
  .populate('supplier_id', 'business_name');

  expiringBatches.forEach(batch => {
    const daysToExpiry = Math.ceil((batch.dates.expiry_date - new Date()) / (1000 * 60 * 60 * 24));
    const severity = daysToExpiry <= 0 ? 'critical' : daysToExpiry <= 3 ? 'high' : 'medium';

    alerts.push({
      id: `expiry_${batch._id}`,
      type: 'expiry',
      severity,
      title: daysToExpiry <= 0 ? 'Product Expired' : 'Product Expiring Soon',
      message: `${batch.product_id.name} (Batch: ${batch.batch_number}) ${daysToExpiry <= 0 ? 'has expired' : `expires in ${daysToExpiry} days`}`,
      batch_id: batch._id,
      batch_number: batch.batch_number,
      product_name: batch.product_id.name,
      product_code: batch.product_id.product_code,
      quantity: batch.quantity.current_quantity,
      expiry_date: batch.dates.expiry_date,
      days_until_expiry: daysToExpiry,
      supplier: batch.supplier_id.business_name,
      created_at: new Date(),
      actions: [
        { type: 'view_batch', label: 'View Batch' },
        { type: 'mark_discount', label: 'Mark for Discount' },
        { type: 'record_waste', label: 'Record Waste' }
      ]
    });
  });

  // Quality alerts
  const qualityIssueBatches = await InventoryBatch.find({
    tenant_id,
    'quality.inspection_passed': false,
    status: { $ne: 'depleted' }
  })
  .populate('product_id', 'name product_code')
  .populate('supplier_id', 'business_name');

  qualityIssueBatches.forEach(batch => {
    alerts.push({
      id: `quality_${batch._id}`,
      type: 'quality',
      severity: 'high',
      title: 'Quality Issue Detected',
      message: `Quality issue with ${batch.product_id.name} (Batch: ${batch.batch_number})`,
      batch_id: batch._id,
      batch_number: batch.batch_number,
      product_name: batch.product_id.name,
      product_code: batch.product_id.product_code,
      quantity: batch.quantity.current_quantity,
      inspection_notes: batch.quality.inspection_notes,
      supplier: batch.supplier_id.business_name,
      created_at: new Date(),
      actions: [
        { type: 'view_batch', label: 'View Batch' },
        { type: 'contact_supplier', label: 'Contact Supplier' },
        { type: 'quarantine_batch', label: 'Quarantine Batch' }
      ]
    });
  });

  // Sort alerts by severity and date
  const severityOrder = { critical: 0, high: 1, medium: 2, low: 3 };
  alerts.sort((a, b) => {
    if (severityOrder[a.severity] !== severityOrder[b.severity]) {
      return severityOrder[a.severity] - severityOrder[b.severity];
    }
    return new Date(b.created_at) - new Date(a.created_at);
  });

  return alerts;
};

// Send low stock notification
const sendLowStockNotification = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { product_id, recipient_emails, recipient_phones } = req.body;
    const tenant_id = req.user.tenant_id;

    const product = await Product.findOne({ tenant_id, _id: product_id })
      .populate('supplier_info.primary_supplier_id', 'business_name');

    if (!product) {
      return res.status(404).json({
        success: false,
        message: 'Product not found'
      });
    }

    const subject = `Low Stock Alert: ${product.name}`;
    const htmlContent = `
      <h2>Low Stock Alert</h2>
      <p>The following product is running low on stock:</p>
      <ul>
        <li><strong>Product:</strong> ${product.name}</li>
        <li><strong>Code:</strong> ${product.product_code}</li>
        <li><strong>Current Stock:</strong> ${product.inventory.current_stock} ${product.inventory.unit_of_measure}</li>
        <li><strong>Reorder Level:</strong> ${product.inventory.reorder_level} ${product.inventory.unit_of_measure}</li>
        <li><strong>Supplier:</strong> ${product.supplier_info.primary_supplier_id?.business_name || 'No supplier assigned'}</li>
      </ul>
      <p>Please reorder soon to avoid stockouts.</p>
    `;
    const textContent = `
      Low Stock Alert
      Product: ${product.name}
      Current Stock: ${product.inventory.current_stock} ${product.inventory.unit_of_measure}
      Reorder Level: ${product.inventory.reorder_level} ${product.inventory.unit_of_measure}
      Please reorder soon to avoid stockouts.
    `;

    const results = {
      email: [],
      sms: []
    };

    // Send email notifications
    if (recipient_emails && recipient_emails.length > 0) {
      for (const email of recipient_emails) {
        const result = await sendEmailNotification(tenant_id, email, subject, htmlContent, textContent);
        results.email.push({ recipient: email, ...result });
      }
    }

    // Send SMS notifications
    if (recipient_phones && recipient_phones.length > 0) {
      const smsMessage = `Low Stock Alert: ${product.name} has only ${product.inventory.current_stock} ${product.inventory.unit_of_measure} remaining. Reorder level: ${product.inventory.reorder_level} ${product.inventory.unit_of_measure}.`;
      for (const phone of recipient_phones) {
        const result = await sendSMSNotification(tenant_id, phone, smsMessage);
        results.sms.push({ recipient: phone, ...result });
      }
    }

    res.json({
      success: true,
      message: 'Low stock notifications sent',
      data: { results }
    });
  } catch (error) {
    console.error('Send low stock notification error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to send low stock notification',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Send expiry notification
const sendExpiryNotification = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { batch_id, recipient_emails, recipient_phones } = req.body;
    const tenant_id = req.user.tenant_id;

    const batch = await InventoryBatch.findOne({ tenant_id, _id: batch_id })
      .populate('product_id', 'name product_code')
      .populate('supplier_id', 'business_name');

    if (!batch) {
      return res.status(404).json({
        success: false,
        message: 'Batch not found'
      });
    }

    const daysToExpiry = Math.ceil((batch.dates.expiry_date - new Date()) / (1000 * 60 * 60 * 24));
    const isExpired = daysToExpiry <= 0;

    const subject = isExpired ? `Product Expired: ${batch.product_id.name}` : `Product Expiring Soon: ${batch.product_id.name}`;
    const htmlContent = `
      <h2>${isExpired ? 'Product Expired' : 'Product Expiring Soon'}</h2>
      <p>The following product batch ${isExpired ? 'has expired' : `expires in ${daysToExpiry} days`}:</p>
      <ul>
        <li><strong>Product:</strong> ${batch.product_id.name}</li>
        <li><strong>Code:</strong> ${batch.product_id.product_code}</li>
        <li><strong>Batch Number:</strong> ${batch.batch_number}</li>
        <li><strong>Quantity:</strong> ${batch.quantity.current_quantity} ${batch.quantity.unit}</li>
        <li><strong>Expiry Date:</strong> ${batch.dates.expiry_date.toLocaleDateString()}</li>
        <li><strong>Supplier:</strong> ${batch.supplier_id.business_name}</li>
      </ul>
      <p>${isExpired ? 'Please remove from inventory immediately.' : 'Please plan for discount or removal to prevent waste.'}</p>
    `;
    const textContent = `
      ${isExpired ? 'Product Expired' : 'Product Expiring Soon'}
      Product: ${batch.product_id.name}
      Batch: ${batch.batch_number}
      Quantity: ${batch.quantity.current_quantity} ${batch.quantity.unit}
      Expiry Date: ${batch.dates.expiry_date.toLocaleDateString()}
      ${isExpired ? 'Remove from inventory immediately.' : `Expires in ${daysToExpiry} days.`}
    `;

    const results = {
      email: [],
      sms: []
    };

    // Send email notifications
    if (recipient_emails && recipient_emails.length > 0) {
      for (const email of recipient_emails) {
        const result = await sendEmailNotification(tenant_id, email, subject, htmlContent, textContent);
        results.email.push({ recipient: email, ...result });
      }
    }

    // Send SMS notifications
    if (recipient_phones && recipient_phones.length > 0) {
      const smsMessage = `${isExpired ? 'Expired' : 'Expiring in ' + daysToExpiry + ' days'}: ${batch.product_id.name} (Batch: ${batch.batch_number}) - ${batch.quantity.current_quantity} ${batch.quantity.unit}.`;
      for (const phone of recipient_phones) {
        const result = await sendSMSNotification(tenant_id, phone, smsMessage);
        results.sms.push({ recipient: phone, ...result });
      }
    }

    res.json({
      success: true,
      message: 'Expiry notifications sent',
      data: { results }
    });
  } catch (error) {
    console.error('Send expiry notification error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to send expiry notification',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Send customer notification
const sendCustomerNotification = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { customer_id, message_type, custom_message } = req.body;
    const tenant_id = req.user.tenant_id;

    const customer = await Customer.findOne({ tenant_id, _id: customer_id });
    if (!customer) {
      return res.status(404).json({
        success: false,
        message: 'Customer not found'
      });
    }

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    let subject, htmlContent, textContent, smsMessage;

    switch (message_type) {
      case 'promotion':
        subject = `Special Offer from ${tenant.business_name}`;
        htmlContent = `
          <h2>Special Offer!</h2>
          <p>Dear ${customer.personal_info.first_name},</p>
          <p>${custom_message || 'We have special offers on our premium meat products. Visit us today!'}</p>
          <p>Thank you for choosing ${tenant.business_name}!</p>
        `;
        textContent = `Special offer from ${tenant.business_name}. ${custom_message || 'Visit us for special deals on premium meat products!'}`;
        smsMessage = `Special offer from ${tenant.business_name}! ${custom_message || 'Visit us for amazing deals!'}`;
        break;
      
      case 'loyalty_points':
        subject = `Loyalty Points Update from ${tenant.business_name}`;
        htmlContent = `
          <h2>Loyalty Points Update</h2>
          <p>Dear ${customer.personal_info.first_name},</p>
          <p>Your current loyalty points balance: ${customer.loyalty.points_balance}</p>
          <p>Current tier: ${customer.loyalty.tier}</p>
          <p>Thank you for being a loyal customer!</p>
        `;
        textContent = `Loyalty points update: You have ${customer.loyalty.points_balance} points. Current tier: ${customer.loyalty.tier}.`;
        smsMessage = `Loyalty update: You have ${customer.loyalty.points_balance} points at ${tenant.business_name}!`;
        break;
      
      case 'custom':
        subject = `Message from ${tenant.business_name}`;
        htmlContent = `<p>${custom_message}</p>`;
        textContent = custom_message;
        smsMessage = custom_message;
        break;
      
      default:
        return res.status(400).json({
          success: false,
          message: 'Invalid message type'
        });
    }

    const results = {
      email: null,
      sms: null
    };

    // Send email if customer has email and prefers email
    if (customer.personal_info.email && 
        customer.preferences.preferred_contact_method === 'email' &&
        customer.preferences.marketing_consent.email) {
      const result = await sendEmailNotification(
        tenant_id, 
        customer.personal_info.email, 
        subject, 
        htmlContent, 
        textContent
      );
      results.email = { recipient: customer.personal_info.email, ...result };
    }

    // Send SMS if customer has phone and prefers SMS
    if (customer.personal_info.phone && 
        customer.preferences.preferred_contact_method === 'sms' &&
        customer.preferences.marketing_consent.sms) {
      const result = await sendSMSNotification(tenant_id, customer.personal_info.phone, smsMessage);
      results.sms = { recipient: customer.personal_info.phone, ...result };
    }

    res.json({
      success: true,
      message: 'Customer notification sent',
      data: { results }
    });
  } catch (error) {
    console.error('Send customer notification error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to send customer notification',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Get notification settings
const getNotificationSettings = async (req, res) => {
  try {
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    const settings = {
      email_notifications: {
        enabled: tenant.settings.enable_email_notifications,
        host: process.env.EMAIL_HOST,
        port: process.env.EMAIL_PORT,
        user: process.env.EMAIL_USER ? '***' : null
      },
      sms_notifications: {
        enabled: tenant.settings.enable_sms_notifications,
        phone_number: process.env.TWILIO_PHONE_NUMBER ? '***' : null
      },
      alert_settings: {
        low_stock_threshold: tenant.settings.low_stock_threshold,
        expiry_warning_days: tenant.settings.expiry_warning_days
      }
    };

    res.json({
      success: true,
      data: { settings }
    });
  } catch (error) {
    console.error('Get notification settings error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to get notification settings',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

// Update notification settings
const updateNotificationSettings = async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const { enable_email_notifications, enable_sms_notifications, low_stock_threshold, expiry_warning_days } = req.body;
    const tenant_id = req.user.tenant_id;

    const tenant = await Tenant.findOne({ tenant_id });
    if (!tenant) {
      return res.status(404).json({
        success: false,
        message: 'Tenant not found'
      });
    }

    // Update settings
    tenant.settings.enable_email_notifications = enable_email_notifications !== undefined ? enable_email_notifications : tenant.settings.enable_email_notifications;
    tenant.settings.enable_sms_notifications = enable_sms_notifications !== undefined ? enable_sms_notifications : tenant.settings.enable_sms_notifications;
    tenant.settings.low_stock_threshold = low_stock_threshold !== undefined ? low_stock_threshold : tenant.settings.low_stock_threshold;
    tenant.settings.expiry_warning_days = expiry_warning_days !== undefined ? expiry_warning_days : tenant.settings.expiry_warning_days;

    await tenant.save();

    res.json({
      success: true,
      message: 'Notification settings updated successfully',
      data: { settings: tenant.settings }
    });
  } catch (error) {
    console.error('Update notification settings error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update notification settings',
      error: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};

module.exports = {
  getNotifications,
  sendLowStockNotification,
  sendExpiryNotification,
  sendCustomerNotification,
  getNotificationSettings,
  updateNotificationSettings,
  getInventoryAlerts
};
