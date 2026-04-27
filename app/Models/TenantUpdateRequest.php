<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUpdateRequest extends Model
{
    protected $table = 'tenant_update_requests';

    protected $fillable = [
        'user_id',
        'current_version',
        'requested_version',
        'status',
        'requested_at',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
