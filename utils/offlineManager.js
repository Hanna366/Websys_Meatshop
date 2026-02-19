const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const fs = require('fs').promises;

class OfflineManager {
  constructor(tenant_id) {
    this.tenant_id = tenant_id;
    this.dbPath = path.join(__dirname, '..', 'offline_data', `${tenant_id}.db`);
    this.db = null;
  }

  // Initialize offline database
  async initialize() {
    try {
      // Ensure offline_data directory exists
      const dir = path.dirname(this.dbPath);
      await fs.mkdir(dir, { recursive: true });

      return new Promise((resolve, reject) => {
        this.db = new sqlite3.Database(this.dbPath, (err) => {
          if (err) {
            console.error('Error opening offline database:', err);
            reject(err);
          } else {
            console.log(`Offline database initialized for tenant: ${this.tenant_id}`);
            this.createTables().then(resolve).catch(reject);
          }
        });
      });
    } catch (error) {
      console.error('Error initializing offline manager:', error);
      throw error;
    }
  }

  // Create necessary tables
  async createTables() {
    return new Promise((resolve, reject) => {
      const tables = [
        `CREATE TABLE IF NOT EXISTS products (
          id TEXT PRIMARY KEY,
          tenant_id TEXT,
          product_code TEXT,
          name TEXT,
          category TEXT,
          price_per_unit REAL,
          unit_type TEXT,
          tax_rate REAL,
          current_stock REAL,
          unit_of_measure TEXT,
          last_sync DATETIME DEFAULT CURRENT_TIMESTAMP
        )`,
        
        `CREATE TABLE IF NOT EXISTS inventory_batches (
          id TEXT PRIMARY KEY,
          tenant_id TEXT,
          product_id TEXT,
          batch_number TEXT,
          current_quantity REAL,
          unit TEXT,
          expiry_date DATETIME,
          last_sync DATETIME DEFAULT CURRENT_TIMESTAMP
        )`,
        
        `CREATE TABLE IF NOT EXISTS customers (
          id TEXT PRIMARY KEY,
          tenant_id TEXT,
          customer_code TEXT,
          first_name TEXT,
          last_name TEXT,
          phone TEXT,
          email TEXT,
          loyalty_points INTEGER DEFAULT 0,
          last_sync DATETIME DEFAULT CURRENT_TIMESTAMP
        )`,
        
        `CREATE TABLE IF NOT EXISTS offline_sales (
          id TEXT PRIMARY KEY,
          tenant_id TEXT,
          sale_data TEXT, -- JSON string
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          synced BOOLEAN DEFAULT FALSE,
          sync_attempts INTEGER DEFAULT 0,
          last_sync_attempt DATETIME,
          sync_error TEXT
        )`,
        
        `CREATE TABLE IF NOT EXISTS sync_queue (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          operation_type TEXT, -- 'create', 'update', 'delete'
          table_name TEXT,
          record_id TEXT,
          record_data TEXT, -- JSON string
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          processed BOOLEAN DEFAULT FALSE,
          error_message TEXT
        )`
      ];

      let completed = 0;
      const total = tables.length;

      tables.forEach(sql => {
        this.db.run(sql, (err) => {
          if (err) {
            console.error('Error creating table:', err);
            reject(err);
          } else {
            completed++;
            if (completed === total) {
              console.log('Offline database tables created successfully');
              resolve();
            }
          }
        });
      });
    });
  }

  // Sync data from main database
  async syncFromMain(mainData) {
    try {
      const { products, batches, customers } = mainData;

      // Clear existing data
      await this.clearTables();

      // Insert products
      if (products && products.length > 0) {
        await this.insertProducts(products);
      }

      // Insert inventory batches
      if (batches && batches.length > 0) {
        await this.insertBatches(batches);
      }

      // Insert customers
      if (customers && customers.length > 0) {
        await this.insertCustomers(customers);
      }

      console.log(`Offline sync completed for tenant: ${this.tenant_id}`);
    } catch (error) {
      console.error('Error syncing from main database:', error);
      throw error;
    }
  }

  // Clear all tables
  async clearTables() {
    const tables = ['products', 'inventory_batches', 'customers'];
    
    for (const table of tables) {
      await this.runQuery(`DELETE FROM ${table}`);
    }
  }

  // Insert products
  async insertProducts(products) {
    const stmt = this.db.prepare(`
      INSERT OR REPLACE INTO products 
      (id, tenant_id, product_code, name, category, price_per_unit, unit_type, tax_rate, current_stock, unit_of_measure)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    for (const product of products) {
      await new Promise((resolve, reject) => {
        stmt.run([
          product._id.toString(),
          product.tenant_id,
          product.product_code,
          product.name,
          product.category,
          product.pricing.price_per_unit,
          product.pricing.unit_type,
          product.pricing.tax_rate,
          product.inventory.current_stock,
          product.inventory.unit_of_measure
        ], (err) => {
          if (err) reject(err);
          else resolve();
        });
      });
    }

    stmt.finalize();
  }

  // Insert inventory batches
  async insertBatches(batches) {
    const stmt = this.db.prepare(`
      INSERT OR REPLACE INTO inventory_batches 
      (id, tenant_id, product_id, batch_number, current_quantity, unit, expiry_date)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `);

    for (const batch of batches) {
      await new Promise((resolve, reject) => {
        stmt.run([
          batch._id.toString(),
          batch.tenant_id,
          batch.product_id.toString(),
          batch.batch_number,
          batch.quantity.current_quantity,
          batch.quantity.unit,
          batch.dates.expiry_date
        ], (err) => {
          if (err) reject(err);
          else resolve();
        });
      });
    }

    stmt.finalize();
  }

  // Insert customers
  async insertCustomers(customers) {
    const stmt = this.db.prepare(`
      INSERT OR REPLACE INTO customers 
      (id, tenant_id, customer_code, first_name, last_name, phone, email, loyalty_points)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `);

    for (const customer of customers) {
      await new Promise((resolve, reject) => {
        stmt.run([
          customer._id.toString(),
          customer.tenant_id,
          customer.customer_code,
          customer.personal_info.first_name,
          customer.personal_info.last_name,
          customer.personal_info.phone,
          customer.personal_info.email,
          customer.loyalty.points_balance || 0
        ], (err) => {
          if (err) reject(err);
          else resolve();
        });
      });
    }

    stmt.finalize();
  }

  // Get products for offline POS
  async getProducts() {
    return new Promise((resolve, reject) => {
      this.db.all(`
        SELECT * FROM products 
        WHERE current_stock > 0 
        ORDER BY name
      `, (err, rows) => {
        if (err) reject(err);
        else resolve(rows);
      });
    });
  }

  // Get customers for offline POS
  async getCustomers() {
    return new Promise((resolve, reject) => {
      this.db.all(`
        SELECT * FROM customers 
        ORDER BY last_name, first_name
      `, (err, rows) => {
        if (err) reject(err);
        else resolve(rows);
      });
    });
  }

  // Get available inventory for a product
  async getAvailableInventory(productId) {
    return new Promise((resolve, reject) => {
      this.db.all(`
        SELECT * FROM inventory_batches 
        WHERE product_id = ? AND current_quantity > 0 
        ORDER BY expiry_date ASC
      `, [productId], (err, rows) => {
        if (err) reject(err);
        else resolve(rows);
      });
    });
  }

  // Save offline sale
  async saveOfflineSale(saleData) {
    const saleId = `offline_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    
    return new Promise((resolve, reject) => {
      this.db.run(`
        INSERT INTO offline_sales 
        (id, tenant_id, sale_data, created_at, synced)
        VALUES (?, ?, ?, ?, ?)
      `, [
        saleId,
        this.tenant_id,
        JSON.stringify(saleData),
        new Date().toISOString(),
        false
      ], function(err) {
        if (err) reject(err);
        else resolve(saleId);
      });
    });
  }

  // Get pending offline sales
  async getPendingSales() {
    return new Promise((resolve, reject) => {
      this.db.all(`
        SELECT * FROM offline_sales 
        WHERE synced = FALSE 
        ORDER BY created_at ASC
      `, (err, rows) => {
        if (err) reject(err);
        else {
          const sales = rows.map(row => ({
            ...row,
            sale_data: JSON.parse(row.sale_data)
          }));
          resolve(sales);
        }
      });
    });
  }

  // Mark sale as synced
  async markSaleSynced(saleId) {
    return new Promise((resolve, reject) => {
      this.db.run(`
        UPDATE offline_sales 
        SET synced = TRUE, last_sync_attempt = CURRENT_TIMESTAMP 
        WHERE id = ?
      `, [saleId], function(err) {
        if (err) reject(err);
        else resolve(this.changes);
      });
    });
  }

  // Update sale sync attempt
  async updateSaleSyncAttempt(saleId, error = null) {
    return new Promise((resolve, reject) => {
      this.db.run(`
        UPDATE offline_sales 
        SET sync_attempts = sync_attempts + 1, 
            last_sync_attempt = CURRENT_TIMESTAMP,
            sync_error = ?
        WHERE id = ?
      `, [error, saleId], function(err) {
        if (err) reject(err);
        else resolve(this.changes);
      });
    });
  }

  // Add operation to sync queue
  async addToSyncQueue(operationType, tableName, recordId, recordData) {
    return new Promise((resolve, reject) => {
      this.db.run(`
        INSERT INTO sync_queue 
        (operation_type, table_name, record_id, record_data)
        VALUES (?, ?, ?, ?)
      `, [
        operationType,
        tableName,
        recordId,
        JSON.stringify(recordData)
      ], function(err) {
        if (err) reject(err);
        else resolve(this.lastID);
      });
    });
  }

  // Get pending sync operations
  async getPendingSyncOperations() {
    return new Promise((resolve, reject) => {
      this.db.all(`
        SELECT * FROM sync_queue 
        WHERE processed = FALSE 
        ORDER BY created_at ASC
      `, (err, rows) => {
        if (err) reject(err);
        else {
          const operations = rows.map(row => ({
            ...row,
            record_data: JSON.parse(row.record_data)
          }));
          resolve(operations);
        }
      });
    });
  }

  // Mark sync operation as processed
  async markSyncProcessed(operationId) {
    return new Promise((resolve, reject) => {
      this.db.run(`
        UPDATE sync_queue 
        SET processed = TRUE 
        WHERE id = ?
      `, [operationId], function(err) {
        if (err) reject(err);
        else resolve(this.changes);
      });
    });
  }

  // Helper method to run queries
  async runQuery(sql, params = []) {
    return new Promise((resolve, reject) => {
      this.db.run(sql, params, function(err) {
        if (err) reject(err);
        else resolve(this);
      });
    });
  }

  // Close database connection
  async close() {
    return new Promise((resolve, reject) => {
      if (this.db) {
        this.db.close((err) => {
          if (err) {
            console.error('Error closing offline database:', err);
            reject(err);
          } else {
            console.log(`Offline database closed for tenant: ${this.tenant_id}`);
            resolve();
          }
        });
      } else {
        resolve();
      }
    });
  }

  // Get database statistics
  async getStats() {
    return new Promise((resolve, reject) => {
      const queries = [
        'SELECT COUNT(*) as count FROM products',
        'SELECT COUNT(*) as count FROM inventory_batches',
        'SELECT COUNT(*) as count FROM customers',
        'SELECT COUNT(*) as count FROM offline_sales WHERE synced = FALSE',
        'SELECT COUNT(*) as count FROM sync_queue WHERE processed = FALSE'
      ];

      let completed = 0;
      const results = {};

      queries.forEach((query, index) => {
        this.db.get(query, (err, row) => {
          if (err) {
            reject(err);
          } else {
            const keys = ['products', 'batches', 'customers', 'pending_sales', 'pending_syncs'];
            results[keys[index]] = row.count;
            
            completed++;
            if (completed === queries.length) {
              resolve(results);
            }
          }
        });
      });
    });
  }
}

module.exports = OfflineManager;
