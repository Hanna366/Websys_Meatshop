<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'supplier_code',
        'name',
        'email',
        'phone',
        'address',
        'details',
        'business_info',
        'business_details',
        'product_categories',
        'payment_terms',
        'delivery_info',
        'quality_standards',
        'performance_metrics',
        'status',
    ];

    protected $casts = [
        'address' => 'array',
        'details' => 'array',
        'business_info' => 'array',
        'business_details' => 'array',
        'product_categories' => 'array',
        'payment_terms' => 'array',
        'delivery_info' => 'array',
        'quality_standards' => 'array',
        'performance_metrics' => 'array',
    ];
}