<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
