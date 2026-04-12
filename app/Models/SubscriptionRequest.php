<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRequest extends Model
{
    use HasFactory;

    protected $table = 'subscription_requests';

    /**
     * Ensure this model always uses the central database connection so tenant
     * connection swapping does not break reads.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $central = config('tenancy.database.central_connection', config('database.default'));
        $this->setConnection($central);
    }

    protected $fillable = [
        'tenant_id',
        'requested_plan',
        'payment_method',
        'payment_reference',
        'amount',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'float',
    ];
}
