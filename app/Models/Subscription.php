<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'price',
        'status',
        'starts_at',
        'expires_at',
        'payment_method',
        'last_payment_at',
        'next_billing_at',
        'auto_renew',
        'features_used',
        'subscription_id'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'auto_renew' => 'boolean',
        'features_used' => 'array'
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Get plan features
     */
    public function getPlanFeatures()
    {
        $features = [
            'basic' => [
                'name' => 'Basic',
                'price' => 29,
                'products_limit' => 100,
                'users_limit' => 1,
                'features' => [
                    'inventory_tracking' => true,
                    'stock_alerts' => true,
                    'pos_system' => false,
                    'supplier_management' => false,
                    'customer_management' => false,
                    'basic_reports' => false,
                    'data_export' => false,
                    'api_access' => false,
                    'advanced_analytics' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                    'sms_notifications' => false
                ]
            ],
            'standard' => [
                'name' => 'Standard',
                'price' => 79,
                'products_limit' => -1, // unlimited
                'users_limit' => 3,
                'features' => [
                    'inventory_tracking' => true,
                    'stock_alerts' => true,
                    'pos_system' => true,
                    'supplier_management' => true,
                    'customer_management' => true,
                    'basic_reports' => true,
                    'data_export' => true,
                    'api_access' => false,
                    'advanced_analytics' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                    'sms_notifications' => false
                ]
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 149,
                'products_limit' => -1, // unlimited
                'users_limit' => -1, // unlimited
                'features' => [
                    'inventory_tracking' => true,
                    'stock_alerts' => true,
                    'pos_system' => true,
                    'supplier_management' => true,
                    'customer_management' => true,
                    'basic_reports' => true,
                    'data_export' => true,
                    'api_access' => true,
                    'advanced_analytics' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'sms_notifications' => true
                ]
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => null, // custom pricing
                'products_limit' => -1, // unlimited
                'users_limit' => -1, // unlimited
                'features' => [
                    'inventory_tracking' => true,
                    'stock_alerts' => true,
                    'pos_system' => true,
                    'supplier_management' => true,
                    'customer_management' => true,
                    'basic_reports' => true,
                    'data_export' => true,
                    'api_access' => true,
                    'advanced_analytics' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'sms_notifications' => true,
                    'dedicated_database' => true,
                    'custom_integrations' => true,
                    'sla_support' => true,
                    'on_premise_deployment' => true
                ]
            ]
        ];

        return $features[$this->plan] ?? [];
    }

    /**
     * Check if user has access to a specific feature
     */
    public function hasFeature($feature)
    {
        $planFeatures = $this->getPlanFeatures();
        return $planFeatures['features'][$feature] ?? false;
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration()
    {
        if ($this->expires_at) {
            return now()->diffInDays($this->expires_at, false);
        }
        return 0;
    }

    /**
     * Extend subscription
     */
    public function extend($days)
    {
        $this->expires_at = $this->expires_at->addDays($days);
        $this->status = 'active';
        return $this->save();
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $this->auto_renew = false;
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Renew subscription
     */
    public function renew()
    {
        $planFeatures = $this->getPlanFeatures();
        $this->expires_at = now()->addMonth();
        $this->status = 'active';
        $this->auto_renew = true;
        $this->last_payment_at = now();
        $this->next_billing_at = now()->addMonth();
        return $this->save();
    }
}
