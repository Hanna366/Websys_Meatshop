<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

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
        'status'
    ];

    protected $casts = [
        'business_address' => 'array',
        'subscription' => 'array',
        'settings' => 'array',
        'usage' => 'array',
        'limits' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active'
    ];

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
        $planFeatures = [
            'basic' => ['inventory_tracking'],
            'standard' => ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'basic_reporting'],
            'premium' => ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'advanced_reporting', 'api_access', 'batch_operations', 'data_export'],
            'enterprise' => ['inventory_tracking', 'pos_system', 'supplier_management', 'customer_management', 'advanced_reporting', 'api_access', 'batch_operations', 'data_export', 'custom_integrations']
        ];

        $plan = $this->subscription['plan'] ?? 'basic';
        return in_array($feature, $planFeatures[$plan] ?? []);
    }

    /**
     * Check if tenant is within usage limits.
     */
    public function isWithinLimit($limitType)
    {
        $usage = $this->usage;
        $limits = $this->limits;

        switch ($limitType) {
            case 'users':
                return ($usage['users_count'] ?? 0) < ($limits['max_users'] ?? 1);
            case 'products':
                return ($usage['products_count'] ?? 0) < ($limits['max_products'] ?? 100);
            case 'storage':
                return ($usage['storage_used'] ?? 0) < ($limits['max_storage_mb'] ?? 1000);
            case 'api_calls':
                return ($usage['api_calls_this_month'] ?? 0) < ($limits['max_api_calls_per_month'] ?? 1000);
            default:
                return false;
        }
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
}
