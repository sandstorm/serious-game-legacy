<?php

namespace Archilex\AdvancedTables\Models;

use Archilex\AdvancedTables\Models\Concerns\InteractsWithTenant;
use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ManagedPresetView extends Model implements Sortable
{
    use HasFactory;
    use InteractsWithTenant;
    use SortableTrait;

    protected $table = 'filament_filter_sets_managed_preset_views';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'is_favorite' => 'bool',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery()
    {
        return static::query()
            ->where('user_id', $this->user_id)
            ->when(Config::hasTenancy(), fn ($query) => $query->where(Config::getTenantColumn(), Config::getTenantId()));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::getUser(), 'user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Config::getTenant(), 'tenant_id');
    }

    public function scopeFavorite($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeBelongsToCurrentUser($query)
    {
        return $query->where('user_id', Config::auth()->id());
    }

    public function scopeDoesntBelongToCurrentUser($query)
    {
        return $query->where('user_id', '!=', Config::auth()->id());
    }
}
