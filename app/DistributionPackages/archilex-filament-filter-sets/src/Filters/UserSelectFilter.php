<?php

namespace Archilex\AdvancedTables\Filters;

use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;

class UserSelectFilter extends SelectFilter
{
    public function getFormField(): Select
    {
        $field = $this->getUserSelect();

        if (filled($defaultState = $this->getDefaultState())) {
            $field->default($defaultState);
        }

        return $field;
    }

    protected function getUserSelect(): Select
    {
        return Config::resourceLoadsAllUsers()
            ?
            Select::make('values')
                ->label(__('advanced-tables::advanced-tables.forms.user'))
                ->placeholder($this->getPlaceholder())
                ->searchable()
                ->multiple()
                ->options(
                    fn () => Config::getUser()::query()
                        ->orderBy(Config::getUserTableNameColumn())
                        ->pluck(Config::getUserTableNameColumn(), Config::getUserTableKeyColumn())
                )
                ->debounce(200)
            :
            Select::make('values')
                ->label(__('advanced-tables::advanced-tables.forms.user'))
                ->placeholder($this->getPlaceholder())
                ->searchable()
                ->multiple()
                ->options(
                    fn () => Config::getUser()::query()
                        ->orderBy(Config::getUserTableNameColumn())
                        ->limit(25)
                        ->pluck(Config::getUserTableNameColumn(), Config::getUserTableKeyColumn())
                )
                ->getSearchResultsUsing(
                    fn (string $search) => Config::getUser()::query()
                        ->where(Config::getUserTableNameColumn(), 'like', "%{$search}%")
                        ->limit(25)
                        ->pluck(Config::getUserTableNameColumn(), Config::getUserTableKeyColumn())
                )
                ->getOptionLabelsUsing(fn ($values): array => Config::getUser()::find($values)?->pluck(Config::getUserTableNameColumn(), Config::getUserTableKeyColumn())->toArray())
                ->debounce(200);
    }
}
