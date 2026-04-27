<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSupportTicket extends Model
{
    protected $table = 'tenant_support_tickets';

    protected $fillable = [
        'user_id',
        'current_version',
        'message',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
