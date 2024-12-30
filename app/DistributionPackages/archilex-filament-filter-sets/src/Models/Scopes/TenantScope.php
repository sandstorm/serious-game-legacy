<?php

namespace Archilex\AdvancedTables\Models\Scopes;

use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(Config::getTenantColumn(), Config::getTenantId());
    }
}
