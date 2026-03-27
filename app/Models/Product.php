<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_code',
        'name',
        'description',
        'category',
        'category_id',
        'subcategory',
        'uom_id',
        'pricing',
        'inventory',
        'batch_tracking',
        'physical_attributes',
        'supplier_info',
        'images',
        'tags',
        'metadata',
        'barcode',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'pricing' => 'array',
        'inventory' => 'array',
        'batch_tracking' => 'array',
        'physical_attributes' => 'array',
        'supplier_info' => 'array',
        'images' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'is_active' => 'bool',
    ];

    public function categoryRef()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function priceListItems()
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class, 'price_list_items')
            ->withPivot(['price', 'min_qty', 'max_qty'])
            ->withTimestamps();
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class, 'product_id');
    }

    public function getCurrentStock(): float
    {
        return (float) ($this->inventory['current_stock'] ?? 0);
    }

    public function getReorderLevel(): float
    {
        return (float) ($this->inventory['reorder_level'] ?? 0);
    }

    public function isLowStock(): bool
    {
        return $this->getCurrentStock() <= $this->getReorderLevel();
    }
}