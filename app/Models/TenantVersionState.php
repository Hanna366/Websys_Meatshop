<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantVersionState extends Model
{
    use HasFactory;

    protected $table = 'tenant_version_states';

    protected $fillable = [
        'tenant_id',
        'current_version',
        'last_update_at',
        'update_status',
    ];

    protected $casts = [
        'last_update_at' => 'datetime',
    ];
}
