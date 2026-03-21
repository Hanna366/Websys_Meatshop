<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'sale_code',
        'customer_id',
        'user_id',
        'items',
        'total',
        'tax',
        'discount',
        'grand_total',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
        'tax' => 'float',
        'discount' => 'float',
        'grand_total' => 'float',
    ];
}