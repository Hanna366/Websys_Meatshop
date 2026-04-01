<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class DatabaseMonitorService
{
    /**
     * Get database usage statistics for all tenants
     */
    public static function getDatabaseUsageStats()
    {
        $tenants = Tenant::all();
        $stats = [];
        
        foreach ($tenants as $tenant) {
            try {
                // Get tenant database configuration
                $tenantDbConfig = self::getTenantDatabaseConfig($tenant);
                
                if (!$tenantDbConfig) {
                    $stats[$tenant->tenant_id] = [
                        'tenant_id' => $tenant->tenant_id,
                        'business_name' => $tenant->business_name,
                        'database_name' => $tenant->database_name ?? 'N/A',
                        'database_size' => 'N/A',
                        'table_count' => 0,
                        'is_available' => false,
                        'last_checked' => now(),
                        'response_time' => 'N/A',
                        'largest_table' => 'N/A',
                        'error' => 'Database configuration not found',
                    ];
                    continue;
                }
                
                // Test database connectivity and get stats
                $databaseInfo = self::getDatabaseInfoDirect($tenantDbConfig);
                
                $stats[$tenant->tenant_id] = [
                    'tenant_id' => $tenant->tenant_id,
                    'business_name' => $tenant->business_name,
                    'database_name' => $tenantDbConfig['database'],
                    'database_size' => $databaseInfo['size'],
                    'table_count' => $databaseInfo['table_count'],
                    'is_available' => $databaseInfo['is_available'],
                    'last_checked' => now(),
                    'response_time' => $databaseInfo['response_time'],
                    'largest_table' => $databaseInfo['largest_table'],
                    'error' => $databaseInfo['error'] ?? null,
                ];
                
            } catch (\Exception $e) {
                // If tenant connection fails, record the error
                $stats[$tenant->tenant_id] = [
                    'tenant_id' => $tenant->tenant_id,
                    'business_name' => $tenant->business_name,
                    'database_name' => $tenant->database_name ?? 'N/A',
                    'database_size' => 'N/A',
                    'table_count' => 0,
                    'is_available' => false,
                    'last_checked' => now(),
                    'response_time' => 'N/A',
                    'largest_table' => 'N/A',
                    'error' => $e->getMessage(),
                ];
                
                Log::error("Database check failed for tenant {$tenant->tenant_id}: " . $e->getMessage());
            }
        }
        
        return $stats;
    }
    
    /**
     * Get tenant database configuration
     */
    private static function getTenantDatabaseConfig($tenant)
    {
        // Default tenant database naming pattern
        $databaseName = $tenant->database_name ?? 'tenant_' . $tenant->tenant_id;
        
        // For XAMPP, use the same connection details as main database
        return [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    }
    
    /**
     * Get database information using direct connection
     */
    private static function getDatabaseInfoDirect($config)
    {
        $startTime = microtime(true);
        
        try {
            // First check if MySQL is running
            $xamppStatus = self::checkXAMPPStatus();
            if (!$xamppStatus['is_running']) {
                return [
                    'size' => 'N/A',
                    'table_count' => 0,
                    'response_time' => round((microtime(true) - $startTime) * 1000, 2) . ' ms',
                    'largest_table' => 'N/A',
                    'is_available' => false,
                    'error' => $xamppStatus['error'] ?? 'MySQL server not available',
                ];
            }
            
            // Create a new database connection
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_TIMEOUT => 3,
                ]
            );
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Get database size and table information
            $sizeQuery = $pdo->prepare("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                    COUNT(*) AS table_count
                FROM information_schema.tables 
                WHERE table_schema = :database_name
            ");
            
            $sizeQuery->execute([':database_name' => $config['database']]);
            $sizeResult = $sizeQuery->fetch();
            
            $size = $sizeResult['size_mb'] ?? 0;
            $tableCount = $sizeResult['table_count'] ?? 0;
            
            // Get largest table
            $largestTableQuery = $pdo->prepare("
                SELECT 
                    table_name,
                    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = :database_name
                ORDER BY (data_length + index_length) DESC 
                LIMIT 1
            ");
            
            $largestTableQuery->execute([':database_name' => $config['database']]);
            $largestTable = $largestTableQuery->fetch();
            
            return [
                'size' => $size . ' MB',
                'table_count' => $tableCount,
                'response_time' => $responseTime . ' ms',
                'largest_table' => $largestTable ? $largestTable['table_name'] . ' (' . $largestTable['size_mb'] . ' MB)' : 'N/A',
                'is_available' => true,
                'error' => null,
            ];
            
        } catch (\PDOException $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            $errorMessage = $e->getMessage();
            
            // Provide user-friendly error messages
            if (strpos($errorMessage, 'Access denied') !== false) {
                $userError = "Database authentication failed";
            } elseif (strpos($errorMessage, "Unknown database") !== false) {
                $userError = "Database '{$config['database']}' does not exist";
            } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                $userError = "MySQL server not running";
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $userError = "Database connection timed out";
            } else {
                $userError = "Database connection failed";
            }
            
            return [
                'size' => 'N/A',
                'table_count' => 0,
                'response_time' => $responseTime . ' ms',
                'largest_table' => 'N/A',
                'is_available' => false,
                'error' => $userError,
            ];
        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'size' => 'N/A',
                'table_count' => 0,
                'response_time' => $responseTime . ' ms',
                'largest_table' => 'N/A',
                'is_available' => false,
                'error' => 'Unexpected error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get overall database statistics summary
     */
    public static function getDatabaseSummary()
    {
        $stats = self::getDatabaseUsageStats();
        
        $totalTenants = count($stats);
        $availableTenants = count(array_filter($stats, fn($stat) => $stat['is_available']));
        $totalSize = 0;
        $totalTables = 0;
        
        foreach ($stats as $stat) {
            if ($stat['is_available'] && is_numeric(str_replace(' MB', '', $stat['database_size']))) {
                $totalSize += (float) str_replace(' MB', '', $stat['database_size']);
                $totalTables += $stat['table_count'];
            }
        }
        
        return [
            'total_tenants' => $totalTenants,
            'available_tenants' => $availableTenants,
            'unavailable_tenants' => $totalTenants - $availableTenants,
            'total_database_size' => round($totalSize, 2) . ' MB',
            'total_tables' => $totalTables,
            'availability_percentage' => $totalTenants > 0 ? round(($availableTenants / $totalTenants) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get database health status
     */
    public static function getDatabaseHealth()
    {
        $summary = self::getDatabaseSummary();
        
        if ($summary['availability_percentage'] >= 95) {
            return [
                'status' => 'healthy',
                'color' => 'emerald',
                'message' => 'All databases are running smoothly',
            ];
        } elseif ($summary['availability_percentage'] >= 80) {
            return [
                'status' => 'warning',
                'color' => 'amber',
                'message' => 'Some databases may need attention',
            ];
        } else {
            return [
                'status' => 'critical',
                'color' => 'rose',
                'message' => 'Multiple database issues detected',
            ];
        }
    }
    
    /**
     * Check if XAMPP MySQL is running
     */
    public static function checkXAMPPStatus()
    {
        try {
            // First check if MySQL port is open
            $socket = @fsockopen(env('DB_HOST', '127.0.0.1'), env('DB_PORT', '3306'), $errno, $errstr, 2);
            
            if (!$socket) {
                return [
                    'is_running' => false,
                    'version' => null,
                    'error' => "MySQL server is not running or not accessible on port " . env('DB_PORT', '3306'),
                ];
            }
            fclose($socket);
            
            // Try to connect with authentication
            $pdo = new \PDO(
                "mysql:host=" . env('DB_HOST', '127.0.0.1') . ";port=" . env('DB_PORT', '3306'),
                env('DB_USERNAME', 'root'),
                env('DB_PASSWORD', ''),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_TIMEOUT => 3,
                ]
            );
            
            // Test basic query
            $result = $pdo->query("SELECT 1")->fetch();
            $version = $pdo->query("SELECT VERSION() as version")->fetch()['version'];
            
            return [
                'is_running' => true,
                'version' => $version,
                'error' => null,
            ];
            
        } catch (\PDOException $e) {
            $errorMessage = $e->getMessage();
            
            // Provide user-friendly error messages
            if (strpos($errorMessage, 'Access denied') !== false) {
                $userMessage = "MySQL authentication failed. Check username/password in .env file.";
            } elseif (strpos($errorMessage, 'Connection refused') !== false || strpos($errorMessage, 'server is not running') !== false) {
                $userMessage = "MySQL server is not running. Please start XAMPP MySQL service.";
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $userMessage = "MySQL server connection timed out.";
            } else {
                $userMessage = "MySQL connection error: " . $errorMessage;
            }
            
            return [
                'is_running' => false,
                'version' => null,
                'error' => $userMessage,
            ];
        } catch (\Exception $e) {
            return [
                'is_running' => false,
                'version' => null,
                'error' => "Unexpected error: " . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get all databases in XAMPP MySQL
     */
    public static function getAllDatabases()
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . env('DB_HOST', '127.0.0.1') . ";port=" . env('DB_PORT', '3306'),
                env('DB_USERNAME', 'root'),
                env('DB_PASSWORD', ''),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
            
            $databases = $pdo->query("SHOW DATABASES")->fetchAll(\PDO::FETCH_COLUMN);
            
            // Filter out system databases
            $userDatabases = array_filter($databases, function($db) {
                return !in_array($db, ['information_schema', 'mysql', 'performance_schema', 'phpmyadmin', 'sys']);
            });
            
            return [
                'success' => true,
                'databases' => $userDatabases,
                'total_count' => count($userDatabases),
                'error' => null,
            ];
            
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'databases' => [],
                'total_count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
}
