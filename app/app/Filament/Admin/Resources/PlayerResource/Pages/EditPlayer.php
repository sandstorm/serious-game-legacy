<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlayerResource\Pages;

use App\Filament\Admin\Resources\PlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlayer extends EditRecord
{
    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
