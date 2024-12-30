<?php

namespace Archilex\AdvancedTables\Forms\Components;

use Archilex\AdvancedTables\Support\Authorize;
use Archilex\AdvancedTables\Support\Config;
use BladeUI\Icons\Factory as IconFactory;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class IconSelect extends Select
{
    public static function getIconLabel(string $icon): string
    {
        return view('advanced-tables::forms.components.icon-result')
            ->with('icon', $icon)
            ->render();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->allowHtml();

        $this->searchable();

        $this->searchValues();

        $this->searchLabels(false);

        $this->searchDebounce(200);

        $this->options($this->getIcons());

        $this->placeholder(__('advanced-tables::advanced-tables.forms.icon.placeholder'));

        $this->optionsLimit(500);
    }

    protected function getIcons(): array
    {
        // Hiding IconSelect still renders the icon templates even though the actual select is hidden.
        // This check makes sure the templates don't render when they aren't supposed to.
        if (! (Authorize::canPerformAction('selectIcon') && Config::hasQuickSaveIconSelect())) {
            return [];
        }

        // Icon lookup is inspired by Lukas Frey's Icon Picker plugin:
        // https://filamentphp.com/plugins/icon-picker

        return Cache::rememberForever(
            'filter_set_icons',
            fn () => collect(App::make(IconFactory::class)->all())
                ->filter(fn ($value, $key) => $key === 'heroicons')
                ->map(
                    fn ($set) => collect($set['paths'])
                        ->map(
                            fn ($path) => collect(File::files($path))
                                ->filter(fn ($file) => Str::endsWith($file, '.svg'))
                                ->reject(fn ($file) => Str::startsWith($file->getFileName(), 'm-'))
                                ->when(
                                    Config::includesOutlineIcons() && ! Config::includesSolidIcons(),
                                    fn ($files) => $files
                                        ->filter(fn ($file) => Str::startsWith($file->getFileName(), 'o-'))
                                )
                                ->when(
                                    ! Config::includesOutlineIcons() && Config::includesSolidIcons(),
                                    fn ($files) => $files
                                        ->filter(fn ($file) => Str::startsWith($file->getFileName(), 's-'))
                                )
                                ->map(
                                    fn ($file) => $set['prefix'] . '-' . $file->getFilenameWithoutExtension()
                                )
                        )
                )
                ->flatten()
                ->mapWithKeys(fn (string $icon) => [$icon => static::getIconLabel($icon)])
                ->toArray()
        );
    }
}
