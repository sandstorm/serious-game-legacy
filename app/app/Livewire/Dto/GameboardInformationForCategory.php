<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class GameboardInformationForCategory
{
    /**
     * @param string $componentName
     * @param CategoryId $title
     * @param ZeitsteinWithColor[] $zeitsteine
     * @param string|null $cardPile
     */
    public function __construct(
        public string $componentName,
        public CategoryId $title,
        public array $zeitsteine,
        public ?string $cardPile = null,
    ) {}

}
