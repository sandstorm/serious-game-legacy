<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class Kompetenzen
{
    /**
     * @param string $ariaLabel
     * @param AbstractIconWithColor[] $kompetenzen
     */
    public function __construct(
        public string $ariaLabel,
        public array $kompetenzen
    ) {
    }

}
