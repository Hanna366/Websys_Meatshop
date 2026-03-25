<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'price_list_id',
        'product_id',
        'price',
        'min_qty',
        'max_qty',
    ];

    protected $casts = [
        'price' => 'float',
        'min_qty' => 'float',
        'max_qty' => 'float',
    ];

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
