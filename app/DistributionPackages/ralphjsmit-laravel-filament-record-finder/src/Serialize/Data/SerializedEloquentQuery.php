<?php

namespace RalphJSmit\Filament\RecordFinder\Serialize\Data;

class SerializedEloquentQuery
{
    public function __construct(
        public readonly string $source,
    ) {}
}
