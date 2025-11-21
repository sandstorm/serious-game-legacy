<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\Definitions\Card\Dto\ResourceChanges;

class EventLogEntry
{
    public function __construct(
        public string $text,
        public ?string $colorClass = null,
        public ?string $playerName = null,
        public ?ResourceChanges $resourceChanges = null,
    ) {}
}
