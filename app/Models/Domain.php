<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Contracts\Domain as DomainContract;
use Stancl\Tenancy\Database\Concerns\ConvertsDomainsToLowercase;
use Stancl\Tenancy\Database\Concerns\EnsuresDomainIsNotOccupied;
use Stancl\Tenancy\Database\Concerns\InvalidatesTenantsResolverCache;

class Domain extends Model implements DomainContract
{
    use EnsuresDomainIsNotOccupied;
    use ConvertsDomainsToLowercase;
    use InvalidatesTenantsResolverCache;

    protected $fillable = [
        'domain',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
