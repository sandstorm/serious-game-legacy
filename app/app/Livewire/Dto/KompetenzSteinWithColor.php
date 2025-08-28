<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class KompetenzSteinWithColor extends AbstractIconWithColor
{
    public function __construct(
        public bool $drawEmpty = true,
        public bool $drawHalfEmpty = false,
        public string $colorClass = '',
        public string $playerName = '',
        public string $iconComponentName = '',
    ) {
        parent::__construct($drawEmpty, $drawHalfEmpty, $colorClass, $playerName, $iconComponentName);
    }

}
