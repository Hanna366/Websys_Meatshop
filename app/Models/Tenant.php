<?php

namespace App\Models;

use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;
use Stancl\Tenancy\Database\Concerns\TenantRun;

class Tenant extends Model implements TenantWithDatabase
{
    use SoftDeletes;
    use HasDatabase;
    use HasDomains;
    use TenantRun;
    use InvalidatesResolverCache;

    protected $fillable = [
        'tenant_id',
        'business_name',
        'business_email',
        'business_phone',
        'business_address',
        'subscription',
        'settings',
        'usage',
        'limits',
        'status',
        'payment_status',
        'suspended_message',
        'domain',
        'db_name',
        'db_username',
        'db_password',
        'plan',
        'plan_started_at',
        'plan_ends_at',
        'admin_name',
        'admin_email'
    ];

    protected $casts = [
        'business_address' => 'array',
        'subscription' => 'array',
        'settings' => 'array',
        'usage' => 'array',
        'limits' => 'array',
        'plan_started_at' => 'datetime',
        'plan_ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active',
        'payment_status' => 'paid',
        'suspended_message' => 'Please contact your administrator.'
    ];

    /**
     * Keep Stancl tenancy key on the numeric primary key to stay compatible with domains table FK.
     */
    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    /**
     * Map Stancl internal keys to existing table columns.
     */
    public function getInternal(string $key)
    {
        $map = [
            'db_name' => 'db_name',
            'db_username' => 'db_username',
            'db_password' => 'db_password',
            'db_connection' => null,
        ];

        return array_key_exists($key, $map) && $map[$key] ? $this->getAttribute($map[$key]) : null;
    }

    public function setInternal(string $key, $value)
    {
        $map = [
            'db_name' => 'db_name',
            'db_username' => 'db_username',
            'db_password' => 'db_password',
        ];

        if (array_key_exists($key, $map)) {
            $this->setAttribute($map[$key], $value);
        }

        return $this;
    }

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the products for the tenant.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the sales for the tenant.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the customers for the tenant.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the suppliers for the tenant.
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Check if tenant has access to a feature.
     */
    public function hasFeature($feature)
    {
        $plan = SubscriptionService::normalizePlan((string) ($this->plan ?? ($this->subscription['plan'] ?? 'basic')));
        $normalizedFeature = SubscriptionService::normalizeFeature((string) $feature);
        $features = SubscriptionService::getPlanFeatures($plan);

        return (bool) ($features[$normalizedFeature] ?? false);
    }

    /**
     * Check if tenant is within usage limits.
     */
    public function isWithinLimit($limitType)
    {
        $usage = is_array($this->usage) ? $this->usage : [];
        $plan = SubscriptionService::normalizePlan((string) ($this->plan ?? ($this->subscription['plan'] ?? 'basic')));
        $effectiveLimits = array_merge(SubscriptionService::getPlanLimits($plan), is_array($this->limits) ? $this->limits : []);

        $usageMap = [
            'users' => 'users_count',
            'products' => 'products_count',
            'storage' => 'storage_used',
            'api_calls' => 'api_calls_this_month',
            'transactions' => 'transactions_this_month',
        ];

        $limitMap = [
            'users' => 'max_users',
            'products' => 'max_products',
            'storage' => 'max_storage_mb',
            'api_calls' => 'max_api_calls_per_month',
            'transactions' => 'max_monthly_transactions',
        ];

        $usageKey = $usageMap[$limitType] ?? null;
        $limitKey = $limitMap[$limitType] ?? null;

        if (!$usageKey || !$limitKey) {
            return false;
        }

        $currentUsage = (int) ($usage[$usageKey] ?? 0);
        $maxAllowed = $effectiveLimits[$limitKey] ?? null;

        if ($maxAllowed === null) {
            return true;
        }

        return $currentUsage <= (int) $maxAllowed;
    }

    /**
     * Update tenant usage statistics.
     */
    public function updateUsage($usageType, $increment = 1)
    {
        $usage = $this->usage ?? [];
        
        switch ($usageType) {
            case 'users':
                $usage['users_count'] = ($usage['users_count'] ?? 0) + $increment;
                break;
            case 'products':
                $usage['products_count'] = ($usage['products_count'] ?? 0) + $increment;
                break;
            case 'storage':
                $usage['storage_used'] = ($usage['storage_used'] ?? 0) + $increment;
                break;
            case 'api_calls':
                $usage['api_calls_this_month'] = ($usage['api_calls_this_month'] ?? 0) + $increment;
                break;
        }
        
        $this->usage = $usage;
        $this->save();
    }

    /**
     * Get tenant statistics.
     */
    public function getStats()
    {
        return [
            'tenant_info' => [
                'tenant_id' => $this->tenant_id,
                'business_name' => $this->business_name,
                'subscription' => $this->subscription,
                'status' => $this->status
            ],
            'usage' => [
                'users' => [
                    'current' => $this->usage['users_count'] ?? 0,
                    'limit' => $this->limits['max_users'] ?? 1,
                    'percentage' => $this->limits['max_users'] > 0 ? 
                        (($this->usage['users_count'] ?? 0) / $this->limits['max_users']) * 100 : 0
                ],
                'products' => [
                    'current' => $this->usage['products_count'] ?? 0,
                    'limit' => $this->limits['max_products'] ?? 100,
                    'percentage' => $this->limits['max_products'] > 0 ? 
                        (($this->usage['products_count'] ?? 0) / $this->limits['max_products']) * 100 : 0
                ],
                'storage' => [
                    'current' => $this->usage['storage_used'] ?? 0,
                    'limit' => $this->limits['max_storage_mb'] ?? 1000,
                    'percentage' => $this->limits['max_storage_mb'] > 0 ? 
                        (($this->usage['storage_used'] ?? 0) / $this->limits['max_storage_mb']) * 100 : 0
                ],
                'api_calls' => [
                    'current' => $this->usage['api_calls_this_month'] ?? 0,
                    'limit' => $this->limits['max_api_calls_per_month'] ?? 1000,
                    'percentage' => $this->limits['max_api_calls_per_month'] > 0 ? 
                        (($this->usage['api_calls_this_month'] ?? 0) / $this->limits['max_api_calls_per_month']) * 100 : 0
                ]
            ],
            'subscription' => [
                'plan' => $this->subscription['plan'],
                'status' => $this->subscription['status'],
                'end_date' => $this->subscription['end_date'],
                'days_until_expiry' => $this->subscription['end_date'] ? 
                    ceil(now()->diffInDays($this->subscription['end_date'])) : null
            ]
        ];
    }

    /**
     * Get decrypted database password.
     */
    public function getDecryptedDbPassword(): ?string
    {
        if (!$this->db_password) {
            return null;
        }

        try {
            return decrypt($this->db_password);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get the tenant database connection configuration.
     */
    public function getTenantDatabaseConfig(): array
    {
        $defaultConnection = config('database.default');
        $baseConfig = config("database.connections.{$defaultConnection}", []);

        $driver = $baseConfig['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            // Tenant databases are stored as sqlite files under database/tenants
            $tenantPath = database_path('tenants');
            if (!is_dir($tenantPath)) {
                mkdir($tenantPath, 0755, true);
            }

            $file = $tenantPath . DIRECTORY_SEPARATOR . ($this->db_name ?? 'tenant') . '.sqlite';

            return [
                'driver' => 'sqlite',
                'database' => $file,
                'prefix' => '',
                'foreign_key_constraints' => $baseConfig['foreign_key_constraints'] ?? true,
            ];
        }

        $preferCentralCredentials = (bool) env('TENANCY_USE_CENTRAL_DB_CREDENTIALS', app()->environment('local'));

        // Default to MySQL-compatible configuration
        return [
            'driver' => $driver,
            'host' => $baseConfig['host'] ?? '127.0.0.1',
            'port' => $baseConfig['port'] ?? '3306',
            'database' => $this->db_name,
            'username' => $preferCentralCredentials
                ? ($baseConfig['username'] ?? null)
                : ($this->db_username ?? ($baseConfig['username'] ?? null)),
            'password' => $preferCentralCredentials
                ? ($baseConfig['password'] ?? null)
                : ($this->getDecryptedDbPassword() ?? ($baseConfig['password'] ?? null)),
            'charset' => $baseConfig['charset'] ?? 'utf8mb4',
            'collation' => $baseConfig['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => $baseConfig['strict'] ?? true,
            'engine' => $baseConfig['engine'] ?? null,
            'options' => $baseConfig['options'] ?? [],
        ];
    }
}
