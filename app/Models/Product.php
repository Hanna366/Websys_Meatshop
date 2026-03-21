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
        'subcategory',
        'pricing',
        'inventory',
        'batch_tracking',
        'physical_attributes',
        'supplier_info',
        'images',
        'tags',
        'barcode',
        'status',
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
    ];

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