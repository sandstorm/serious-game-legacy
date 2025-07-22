<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class ZeitsteinWithColor extends AbstractIconWithColor
{
    public function __construct(
        public bool $drawEmpty = true,
        public string $colorClass = '',
        public string $playerName = ''
    ) {
        parent::__construct($drawEmpty, $colorClass, $playerName, 'gameboard.zeitsteine.zeitstein-icon');
    }

}
