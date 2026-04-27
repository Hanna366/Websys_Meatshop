<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    protected $table = 'tenant_settings';

    protected $fillable = [
        'tenant_id',
        'logo_path',
        'theme',
        'primary_color',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
