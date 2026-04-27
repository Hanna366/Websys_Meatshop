<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'release_name',
        'description',
        'type',
        'status',
        'release_date',
        'features',
        'fixes',
        'requirements',
        'download_url',
        'checksum',
        'is_mandatory',
        'auto_update',
        'is_stable',
        'is_available_to_tenants',
        'is_deprecated',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'features' => 'array',
        'fixes' => 'array',
        'requirements' => 'array',
        'is_mandatory' => 'boolean',
        'auto_update' => 'boolean',
        'is_stable' => 'boolean',
        'is_available_to_tenants' => 'boolean',
        'is_deprecated' => 'boolean',
    ];

    /**
     * Get update logs for this version
     */
    public function updateLogs(): HasMany
    {
        return $this->hasMany(UpdateLog::class, 'to_version', 'version');
    }

    /**
     * Scope to get only stable versions
     */
    public function scopeStable($query)
    {
        return $query->where('status', 'stable');
    }

    /**
     * Scope to get versions newer than given version
     */
    public function scopeNewerThan($query, string $version)
    {
        return $query->where('version', '>', $version);
    }

    /**
     * Get formatted version with type
     */
    public function getFormattedVersionAttribute(): string
    {
        return $this->version . ' (' . ($this->is_stable ? 'Stable' : ucfirst($this->type)) . ')';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_deprecated) return 'Deprecated';
        if ($this->is_stable) return 'Stable';
        return ucfirst($this->status ?? 'Unknown');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'development' => 'amber',
            'testing' => 'blue',
            'stable' => 'emerald',
            'deprecated' => 'rose',
            default => 'slate'
        };
    }

    /**
     * Get type badge color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'major' => 'purple',
            'minor' => 'blue',
            'patch' => 'green',
            'hotfix' => 'red',
            default => 'slate'
        };
    }

    /**
     * Check if version is newer than current
     */
    public function isNewerThan(string $currentVersion): bool
    {
        return version_compare($this->version, $currentVersion, '>');
    }

    /**
     * Get download size in human readable format
     */
    public function getDownloadSizeAttribute(): string
    {
        if (!$this->download_url) return 'N/A';
        
        // This would require actual file size calculation
        // For now, return a placeholder
        return '~10 MB';
    }
}
