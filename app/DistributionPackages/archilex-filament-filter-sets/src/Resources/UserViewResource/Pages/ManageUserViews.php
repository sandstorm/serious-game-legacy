<?php

namespace Archilex\AdvancedTables\Resources\UserViewResource\Pages;

use Archilex\AdvancedTables\AdvancedTables;
use Archilex\AdvancedTables\Resources\UserViewResource;
use Filament\Resources\Pages\ManageRecords;

class ManageUserViews extends ManageRecords
{
    use AdvancedTables;

    protected static string $resource = UserViewResource::class;
}
