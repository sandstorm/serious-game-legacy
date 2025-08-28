<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class KompetenzSteineForCategory
{
    /**
     * @param string $ariaLabel
     * @param AbstractIconWithColor[] $kompetenzSteine
     */
    public function __construct(
        public string $ariaLabel,
        public array $kompetenzSteine
    ) {
    }

}
