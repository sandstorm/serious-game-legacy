@props([
    'placement' => 'bottom-end'
])

<div>
    @if (Archilex\AdvancedTables\Support\Config::showViewManagerAsSlideOver())
        {{ $this->showViewManagerAction() }}
    @else
        <x-advanced-tables::view-manager.dropdown 
            :offset="in_array(Archilex\AdvancedTables\Support\Config::getFavoritesBarTheme(), [Archilex\AdvancedTables\Enums\FavoritesBarTheme::Github, Archilex\AdvancedTables\Enums\FavoritesBarTheme::Filament]) ? 4 : 8"
            :color="Archilex\AdvancedTables\Support\Config::isViewManagerInTable() ? 'gray' : 'primary'"
            :icon="Archilex\AdvancedTables\Support\Config::getViewManagerIcon()"
            :placement="$placement"
        />
    @endif
</div>