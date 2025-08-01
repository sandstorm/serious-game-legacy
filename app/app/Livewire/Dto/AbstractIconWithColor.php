<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

abstract class AbstractIconWithColor
{
    public function __construct(
        public bool $drawEmpty = true,
        public bool $drawHalfEmpty = false,
        public string $colorClass = '',
        public string $playerName = '',
        public string $iconComponentName = '',
    ) {
    }

}
