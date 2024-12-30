<?php

namespace Archilex\AdvancedTables\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Authorize
{
    public static function canPerformAction(string $action, Model | string | null $model = null): bool
    {
        return once(function () use ($action, $model) {
            $model = $model ?? Config::getUserView();

            $policy = Gate::getPolicyFor($model);
            if ($policy === null) {
                return true;
            }

            if (! method_exists($policy, $action)) {
                return true;
            }

            return Gate::check($action, $model);
        });
    }
}
