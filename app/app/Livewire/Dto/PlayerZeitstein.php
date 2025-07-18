<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class PlayerZeitstein
{
    public function __construct(
        public bool $isAvailable,
    ) {
    }

}
