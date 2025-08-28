<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class Zeitsteine
{
    /**
     * @param string $ariaLabel
     * @param ZeitsteinWithColor[] $zeitsteine
     */
    public function __construct(
        public string $ariaLabel,
        public array $zeitsteine
    ) {
    }

}
