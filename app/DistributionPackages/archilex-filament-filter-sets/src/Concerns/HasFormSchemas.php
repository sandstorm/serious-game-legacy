<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Enums\Status;
use Archilex\AdvancedTables\Forms\Components\ColorPicker;
use Archilex\AdvancedTables\Forms\Components\IconSelect;
use Archilex\AdvancedTables\Forms\Components\Summary;
use Archilex\AdvancedTables\Support\Authorize;
use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

trait HasFormSchemas
{
    public static function getUserViewResourceFormSchema(): array
    {
        return [
            Grid::make([
                'default' => 1,
                'sm' => Config::showQuickSaveAsSlideOver() ? 1 : 3,
            ])
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => Config::showQuickSaveAsSlideOver() ? 1 : 2,
                    ])
                        ->schema([
                            Select::make('status')
                                ->label(__('advanced-tables::advanced-tables.forms.status.label'))
                                ->options(Status::class)
                                ->required()
                                ->selectablePlaceholder(false)
                                ->columnSpanFull(),
                            TextInput::make('name')
                                ->label(__('advanced-tables::advanced-tables.forms.name.label'))
                                ->helperText(Config::hasQuickSaveNameHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.name.helper_text')
                                    : '')
                                ->required()
                                ->maxLength(255)
                                ->autocomplete(false)
                                ->columnSpanFull(),
                            IconSelect::make('icon')
                                ->label(__('advanced-tables::advanced-tables.forms.icon.label'))
                                ->extraAttributes(['class' => 'advanced-tables-icon-select'])
                                ->visible(fn () => Authorize::canPerformAction('selectIcon') && Config::hasQuickSaveIconSelect())
                                ->columnSpan(1),
                            ColorPicker::make('color')
                                ->label(__('advanced-tables::advanced-tables.forms.color.label'))
                                ->visible(fn () => Authorize::canPerformAction('selectColor') && Config::hasQuickSaveColorPicker())
                                ->columnSpan(1),
                            Toggle::make('is_public')
                                ->label(__('advanced-tables::advanced-tables.forms.public.label'))
                                ->helperText(Config::hasQuickSavePublicHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.public.helper_text')
                                    : '')
                                ->visible(Authorize::canPerformAction('makePublic'))
                                ->columnSpanFull(),
                            Toggle::make('is_global_favorite')
                                ->label(__('advanced-tables::advanced-tables.forms.global_favorite.label'))
                                ->helperText(Config::hasQuickSaveGlobalFavoriteHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.global_favorite.helper_text')
                                    : '')
                                ->visible(Authorize::canPerformAction('makeGlobalFavorite'))
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => Config::showQuickSaveAsSlideOver() ? 1 : 2,
                        ])
                        ->extraAttributes(['class' => ! Config::showQuickSaveAsSlideOver() ? 'sm:border-r pb-6 sm:pr-[24px] sm:pb-[0px] dark:border-gray-700' : '']),
                    Grid::make(1)
                        ->schema([
                            Summary::make('indicators')
                                ->label(__('advanced-tables::advanced-tables.forms.filters.label'))
                                ->disabled()
                                ->visible(fn (Model $record) => filled($record->indicators)),
                        ])
                        ->columnSpan(1),
                ]),
        ];
    }

    protected function getSaveOptionFormSchema(): array
    {
        return [
            Grid::make([
                'default' => 1,
                'sm' => Config::showQuickSaveAsSlideOver() ? 1 : 3,
            ])
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => Config::showQuickSaveAsSlideOver() ? 1 : 2,
                    ])
                        ->schema([
                            TextInput::make('name')
                                ->label(__('advanced-tables::advanced-tables.forms.name.label'))
                                ->helperText(Config::hasQuickSaveNameHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.name.helper_text')
                                    : '')
                                ->required()
                                ->maxLength(255)
                                ->autocomplete(false)
                                ->columnSpanFull(),
                            IconSelect::make('icon')
                                ->label(__('advanced-tables::advanced-tables.forms.icon.label'))
                                ->extraAttributes(['class' => 'advanced-tables-icon-select'])
                                ->visible(fn () => Authorize::canPerformAction('selectIcon') && Config::hasQuickSaveIconSelect())
                                ->columnSpan(1),
                            ColorPicker::make('color')
                                ->label(__('advanced-tables::advanced-tables.forms.color.label'))
                                ->visible(fn () => Authorize::canPerformAction('selectColor') && Config::hasQuickSaveColorPicker())
                                ->columnSpan(1),
                            Toggle::make('is_managed_by_current_user')
                                ->label(__('advanced-tables::advanced-tables.forms.favorite.label'))
                                ->helperText(Config::hasQuickSaveFavoriteHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.favorite.helper_text')
                                    : '')
                                ->visible(Authorize::canPerformAction('makeFavorite') && Config::hasQuickSaveMakeFavorite())
                                ->columnSpanFull(),
                            Toggle::make('is_public')
                                ->label(__('advanced-tables::advanced-tables.forms.public.label'))
                                ->helperText(Config::hasQuickSavePublicHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.public.helper_text')
                                    : '')
                                ->visible(Authorize::canPerformAction('makePublic') && Config::hasQuickSaveMakePublic())
                                ->columnSpanFull(),
                            Toggle::make('is_global_favorite')
                                ->label(__('advanced-tables::advanced-tables.forms.global_favorite.label'))
                                ->helperText(Config::hasQuickSaveGlobalFavoriteHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.global_favorite.helper_text')
                                    : '')
                                ->visible(Authorize::canPerformAction('makeGlobalFavorite') && Config::hasQuickSaveMakeGlobalFavorite())
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => Config::showQuickSaveAsSlideOver() ? 1 : 2,
                        ])
                        ->extraAttributes(['class' => ! Config::showQuickSaveAsSlideOver() ? 'sm:border-r pb-6 sm:pr-[24px] sm:pb-[0px] dark:border-gray-700' : '']),
                    Grid::make(1)
                        ->schema([
                            Summary::make('indicators')
                                ->label(__('advanced-tables::advanced-tables.forms.filters.label'))
                                ->helperText(Config::hasQuickSaveFiltersHelperText()
                                    ? __('advanced-tables::advanced-tables.forms.filters.helper_text')
                                    : '')
                                ->disabled()
                                ->visible(fn () => filled($this->getMergedFilterIndicators()))
                                ->default(fn () => $this->getMergedFilterIndicators()),
                            Placeholder::make('predefined_note')
                                ->label(__('advanced-tables::advanced-tables.forms.note'))
                                ->content(function () {
                                    if ($label = $this->getActivePresetViewLabel()) {
                                        return new HtmlString('<span class="text-sm text-gray-600 dark:text-gray-300">' . __('advanced-tables::advanced-tables.forms.preset_view.helper_text_start') . '<span class="font-medium text-gray-700 dark:text-gray-300">' . $label . '</span>' . __('advanced-tables::advanced-tables.forms.preset_view.helper_text_end') . '</span>');
                                    }
                                })
                                ->visible(
                                    fn () => property_exists($this, 'activePresetView') &&
                                    $this->getActivePresetView()?->modifiesQuery() &&
                                    Config::hasQuickSaveActivePresetViewHelperText()
                                ),
                        ])
                        ->columnSpan(1),
                ]),
        ];
    }
}
