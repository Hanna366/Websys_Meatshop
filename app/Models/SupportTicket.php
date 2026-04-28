<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $table = 'support_tickets';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'version',
        'current_version',
        'last_update_at',
        'message',
        'status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'last_update_at' => 'datetime',
    ];
}
