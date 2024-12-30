<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasViews
{
    public function managedUserViews(): BelongsToMany
    {
        return $this->belongsToMany(Config::getUserView(), 'filament_filter_set_user', foreignPivotKey: 'user_id', relatedPivotKey: 'filter_set_id')
            ->withPivot('sort_order', 'is_visible');
    }

    public function managedPresetViews(): HasMany
    {
        return $this->hasMany(Config::getManagedPresetView(), 'user_id');
    }
}
