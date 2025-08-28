<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class GameboardInformationForKompetenzenOverview
{
    /**
     * @param CategoryId $title
     * @param Kompetenzen|null $kompetenzen
     */
    public function __construct(
        public CategoryId $title,
        public ?Kompetenzen $kompetenzen,
    ) {}

}
