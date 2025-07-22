<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class GameboardInformationForKompetenzenOverview
{
    /**
     * @param CategoryId $title
     * @param int|null $kompetenzen
     * @param int|null $kompetenzenRequiredByPhase
     */
    public function __construct(
        public CategoryId $title,
        public ?int $kompetenzen,
        public ?int $kompetenzenRequiredByPhase,
    ) {}

}
