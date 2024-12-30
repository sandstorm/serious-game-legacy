<?php

namespace Archilex\AdvancedTables\Models;

use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ManagedUserView extends Pivot
{
    protected $table = 'filament_filter_set_user';

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::getUser(), 'user_id');
    }

    public function userView(): BelongsTo
    {
        return $this->belongsTo(Config::getUserView());
    }
}
