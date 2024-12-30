<?php

namespace RalphJSmit\Filament\RecordFinder\Serialize\Data;

use Laravel\SerializableClosure\SerializableClosure;

class SerializableClosureObject
{
    public function __construct(
        public readonly object $originalWithoutClosureProperties,
        /** @var array<string, SerializableClosure> */
        public readonly array $serializableClosureProperties,
        /** @var array<string, SerializableClosureObject> */
        public readonly array $serializableClosureObjectProperties,
    ) {}
}
