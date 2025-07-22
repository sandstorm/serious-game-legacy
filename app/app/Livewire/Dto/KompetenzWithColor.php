<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class KompetenzWithColor extends AbstractIconWithColor
{
    public function __construct(
        public bool $drawEmpty = true,
        public string $colorClass = '',
        public string $playerName = '',
        public string $iconComponentName = '',
    ) {
        parent::__construct($drawEmpty, $colorClass, $playerName, $iconComponentName);
    }

}
