<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class ZeitsteinWithColor
{
    public function __construct(
        public bool $drawEmpty = true,
        public string $colorClass = '',
    ) {
    }

}
