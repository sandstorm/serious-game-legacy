<?php

namespace Archilex\AdvancedTables\Models\Concerns;

use Archilex\AdvancedTables\Models\Scopes\TenantScope;
use Archilex\AdvancedTables\Support\Config;

trait InteractsWithTenant
{
    protected static function bootInteractsWithTenant(): void
    {
        if (! Config::hasTenancy()) {
            return;
        }

        $tenant = Config::getTenantId();

        static::addGlobalScope(new TenantScope);

        static::creating(fn (self $model) => $model->{Config::getTenantColumn()} = $tenant);
        static::updating(fn (self $model) => $model->{Config::getTenantColumn()} = $tenant);
    }
}
