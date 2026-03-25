<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'precision',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'uom_id');
    }
}
