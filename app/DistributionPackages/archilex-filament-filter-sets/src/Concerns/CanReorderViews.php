<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait CanReorderViews
{
    public function reorderViews(array $order, string $modelName, bool $isFavorite): void
    {
        $model = match ($modelName) {
            'managedUserView' => new (Config::getManagedUserView()),
            'userView' => new (Config::getUserView()),
            'managedPresetView' => new (Config::getManagedPresetView()),
        };

        $modelKeyName = $model->getKeyName();

        if (Config::canManageGlobalUserViews() && $modelName === 'managedUserView' && $isFavorite) {
            $order = $this->buildGlobalUserViewsOrder($order, $model);
        } elseif (Config::canManagePresetViews() && $modelName === 'managedPresetView') {
            $order = $this->buildPresetViewOrder($order, $isFavorite);
        }

        $model
            ->newModelQuery()
            ->whereIn($modelKeyName, array_values($order))
            ->update([
                'sort_order' => DB::raw(
                    'case ' . collect($order)
                        ->map(fn ($recordKey, int $recordIndex): string => 'when ' . $modelKeyName . ' = ' . DB::getPdo()->quote($recordKey) . ' then ' . ($recordIndex + 1))
                        ->implode(' ') . ' end'
                ),
            ]);
    }

    protected function buildGlobalUserViewsOrder(array $order, Model $model): array
    {
        $newGlobals = collect($order)
            ->filter(fn ($value) => Str::startsWith($value, 'new_managed_global_view_'))
            ->map(fn ($value) => Str::after($value, 'new_managed_global_view_'));

        if ($newGlobals->isEmpty()) {
            return $order;
        }

        // Sync any new global views with the user's managed views
        $synced = Config::auth()->user()->managedUserViews()->syncWithoutDetaching($newGlobals);

        $managedUserViews = $model->newModelQuery()->whereIn('filter_set_id', $synced['attached'])->get();

        return collect($order)->map(function ($item) use ($managedUserViews) {
            return Str::startsWith($item, 'new_managed_global_view_')
                ? $managedUserViews->firstWhere('filter_set_id', Str::after($item, 'new_managed_global_view_'))->id
                : $item;
        })->toArray();
    }

    protected function buildPresetViewOrder(array $order, bool $isFavorite): array
    {
        $newPresetViews = collect($order)
            ->filter(fn ($value) => Str::startsWith($value, 'new_managed_preset_view_'))
            ->map(fn ($value) => Str::after($value, 'new_managed_preset_view_'));

        if ($newPresetViews->isEmpty()) {
            return $order;
        }

        // Create any new preset views
        $presetViewsToCreate = collect($this->getCachedPresetViews())
            ->filter(function ($presetView, $presetViewName) use ($newPresetViews) {
                return $newPresetViews->contains($presetViewName);
            })
            ->map(function ($presetView, $presetViewName) use ($isFavorite) {
                $view = [
                    'name' => $presetViewName,
                    'resource' => $this->getResourceName(),
                    'is_favorite' => $isFavorite,
                ];

                if (Config::hasTenancy()) {
                    $view[Config::getTenantColumn()] = Config::getTenantId();
                }

                return $view;
            })
            ->values();

        $managedPresetViews = Config::auth()->user()->managedPresetViews()->createMany($presetViewsToCreate);

        return collect($order)->map(function ($item) use ($managedPresetViews) {
            return Str::startsWith($item, 'new_managed_preset_view_')
                ? $managedPresetViews->firstWhere('name', Str::after($item, 'new_managed_preset_view_'))->id
                : $item;
        })->toArray();
    }
}
