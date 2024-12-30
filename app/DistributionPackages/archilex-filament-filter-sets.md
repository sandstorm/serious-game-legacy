name     : archilex/filament-filter-sets
descrip. : Advanced Tables, previously Filter Sets, supercharges your Filament Tables with advanced tabs, customizable views, reorderable columns, and more
keywords : advanced-tables, archilex, filament, filters, laravel, tables, views
versions : * 3.7.35
released : 2024-12-08, 3 weeks ago
type     : library
license  : proprietary
homepage : https://github.com/archilex/filament-filter-sets
source   : []  
dist     : [zip] https://filament-filter-sets.composer.sh/download/9dac2d2b-0623-4e3e-b124-cb220a889d25/advanced-tables-for-filament-3.7.35.zip 770f95d9fb2faf3df77b0b9538bed38b30999e75
path     : /Users/sebastian/src/laravel-filament-on-docker-kickstart/app/vendor/archilex/filament-filter-sets
names    : archilex/filament-filter-sets

autoload
psr-4
Archilex\AdvancedTables\ => src
Archilex\AdvancedTables\Database\Factories\ => database/factories

requires
archilex/filament-toggle-icon-column ^3.0
filament/filament ^3.2.63
illuminate/contracts ^10.45|^11.0
php ^8.1
spatie/eloquent-sortable ^4.0
spatie/laravel-package-tools ^1.13.5
spatie/once ^3.1

requires (dev)
laravel/pint ^1.0
nunomaduro/collision ^7.0|^8.1
orchestra/testbench ^8.0|^9.0
pestphp/pest ^2.0
pestphp/pest-plugin-laravel ^2.0
pestphp/pest-plugin-livewire ^2.0
phpunit/phpunit ^10.0
spatie/laravel-ray ^1.26
