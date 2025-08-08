<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;

class PlayerListPlayerDto
{
    public function __construct(
        public string                    $name,
        public PlayerId                  $playerId,
        public string                    $playerColorClass,
        public bool                      $isPlayersTurn,
        public Zeitsteine                $zeitsteine,
        public LebenszielPhaseDefinition $phaseDefinition,
        public LebenszielDefinition      $lebenszielDefinition,
        public MoneyAmount               $sumOfAllAssets,
        public MoneyAmount               $sumOfAllLoans,
        public ?JobCardDefinition        $job,
        public MoneyAmount               $gehalt,
        public MoneyAmount               $guthaben,
    )
    {
    }

}
