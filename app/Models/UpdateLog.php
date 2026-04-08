<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'from_version',
        'to_version',
        'status',
        'error_message',
        'update_data',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'update_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the update log
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'amber',
            'downloading' => 'blue',
            'installing' => 'purple',
            'completed' => 'emerald',
            'failed' => 'rose',
            default => 'slate'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => 'clock',
            'downloading' => 'download',
            'installing' => 'settings',
            'completed' => 'check-circle',
            'failed' => 'x-circle',
            default => 'help-circle'
        };
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $duration = $this->completed_at->diffInMinutes($this->started_at);
        
        if ($duration < 1) {
            return 'Less than 1 minute';
        } elseif ($duration < 60) {
            return $duration . ' minute' . ($duration > 1 ? 's' : '');
        } else {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . 
                   ($minutes > 0 ? ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '');
        }
    }

    /**
     * Check if update is currently active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['downloading', 'installing']);
    }

    /**
     * Check if update completed successfully
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if update failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get progress percentage
     */
    public function getProgressAttribute(): int
    {
        return match($this->status) {
            'pending' => 0,
            'downloading' => 50,
            'installing' => 75,
            'completed' => 100,
            'failed' => 0,
            default => 0
        };
    }
}
