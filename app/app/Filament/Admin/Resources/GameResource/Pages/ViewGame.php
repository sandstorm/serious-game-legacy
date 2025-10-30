<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources\GameResource\Pages;

use App\Filament\Admin\Resources\GameResource;
use Filament\Resources\Pages\ViewRecord;

class ViewGame extends ViewRecord
{
    protected static string $resource = GameResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
