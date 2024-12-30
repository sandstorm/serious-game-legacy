<?php

namespace RalphJSmit\Filament\RecordFinder;

use Livewire\Livewire;
use RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRecordFinderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-filament-record-finder')
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        Livewire::component(RecordFinderTable::class);
    }
}
