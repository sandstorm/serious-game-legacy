<?php

namespace Archilex\AdvancedTables\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class PanelPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
}
