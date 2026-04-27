<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $table = 'subscription_payments';

    protected $fillable = [
        'tenant_id', 'user_id', 'plan_id', 'amount', 'billing_cycle', 'payment_method',
        'reference_number', 'proof_path', 'notes', 'status', 'reviewed_by', 'reviewed_at', 'admin_notes'
    ];

    protected $casts = [
        'amount' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
