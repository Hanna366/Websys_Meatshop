<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateRequest extends Model
{
    use HasFactory;

    protected $table = 'update_requests';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'current_version',
        'requested_version',
        'status',
        'notes',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
