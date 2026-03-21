<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'personal_info',
        'address',
        'preferences',
        'loyalty',
        'purchasing_history',
        'payment_methods',
        'special_requirements',
        'business_info',
        'status',
    ];

    protected $casts = [
        'personal_info' => 'array',
        'address' => 'array',
        'preferences' => 'array',
        'loyalty' => 'array',
        'purchasing_history' => 'array',
        'payment_methods' => 'array',
        'special_requirements' => 'array',
        'business_info' => 'array',
    ];
}