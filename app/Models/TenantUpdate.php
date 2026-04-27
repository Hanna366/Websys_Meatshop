<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUpdate extends Model
{
    protected $table = 'tenant_updates';

    protected $fillable = [
        'current_version',
        'available_version',
        'release_notes',
        'last_checked_at',
        'force_update',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'force_update' => 'boolean',
    ];
}
