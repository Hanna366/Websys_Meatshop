<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'username',
        'email',
        'password',
        'role',
        'profile',
        'permissions',
        'preferences',
        'last_login',
        'login_attempts',
        'lock_until',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'profile' => 'array',
        'permissions' => 'array',
        'preferences' => 'array',
        'last_login' => 'datetime',
        'lock_until' => 'datetime',
        'login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active',
        'login_attempts' => 0
    ];

    /**
     * Get the tenant that owns the user.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the sales processed by the user.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'staff.cashier_id');
    }

    /**
     * Get the products created by the user.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    /**
     * Set permissions based on role.
     */
    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value;
        
        // Auto-set permissions based on role
        $permissions = match($value) {
            'owner' => [
                'can_manage_users' => true,
                'can_manage_inventory' => true,
                'can_process_sales' => true,
                'can_view_reports' => true,
                'can_manage_suppliers' => true,
                'can_manage_customers' => true,
                'can_export_data' => true,
                'can_access_api' => true
            ],
            'manager' => [
                'can_manage_users' => false,
                'can_manage_inventory' => true,
                'can_process_sales' => true,
                'can_view_reports' => true,
                'can_manage_suppliers' => true,
                'can_manage_customers' => true,
                'can_export_data' => true,
                'can_access_api' => false
            ],
            'cashier' => [
                'can_manage_users' => false,
                'can_manage_inventory' => false,
                'can_process_sales' => true,
                'can_view_reports' => false,
                'can_manage_suppliers' => false,
                'can_manage_customers' => true,
                'can_export_data' => false,
                'can_access_api' => false
            ],
            'inventory_staff' => [
                'can_manage_users' => false,
                'can_manage_inventory' => true,
                'can_process_sales' => false,
                'can_view_reports' => false,
                'can_manage_suppliers' => false,
                'can_manage_customers' => false,
                'can_export_data' => false,
                'can_access_api' => false
            ],
            default => []
        };
        
        $this->attributes['permissions'] = json_encode($permissions);
    }

    /**
     * Get permissions attribute.
     */
    public function getPermissionsAttribute($value)
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    /**
     * Check if user is locked.
     */
    public function isLocked()
    {
        return $this->lock_until && $this->lock_until->isFuture();
    }

    /**
     * Increment login attempts.
     */
    public function incrementLoginAttempts()
    {
        $this->login_attempts = ($this->login_attempts ?? 0) + 1;
        
        // Lock account after 5 failed attempts for 2 hours
        if ($this->login_attempts >= 5) {
            $this->lock_until = now()->addHours(2);
        }
        
        $this->save();
    }

    /**
     * Reset login attempts.
     */
    public function resetLoginAttempts()
    {
        $this->login_attempts = 0;
        $this->lock_until = null;
        $this->last_login = now();
        $this->save();
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        return $permissions[$permission] ?? false;
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute()
    {
        return ($this->profile['first_name'] ?? '') . ' ' . ($this->profile['last_name'] ?? '');
    }
}
