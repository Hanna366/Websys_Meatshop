<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'channel',
        'currency',
        'status',
        'effective_from',
        'effective_to',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'price_list_items')
            ->withPivot(['price', 'min_qty', 'max_qty'])
            ->withTimestamps();
    }
}
