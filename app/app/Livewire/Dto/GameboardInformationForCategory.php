<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;

class GameboardInformationForCategory
{
    /**
     * @param CategoryEnum $title
     * @param int|null $kompetenzen
     * @param int|null $kompetenzenRequiredByPhase
     * @param int $availableZeitsteine
     * @param ZeitsteineForPlayer[] $placedZeitsteine
     * @param PileId|null $cardPile
     */
    public function __construct(
        public CategoryEnum $title,
        public ?int $kompetenzen,
        public ?int $kompetenzenRequiredByPhase,
        public int $availableZeitsteine,
        public array $placedZeitsteine,
        public ?PileId $cardPile
    ) {}

}
