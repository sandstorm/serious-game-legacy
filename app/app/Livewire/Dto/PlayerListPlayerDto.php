<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\Feature\Initialization\ValueObject\LebenszielPhase;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;

class PlayerListPlayerDto
{
    /**
     * @param ZeitsteinWithColor[] $zeitsteine
     */
    public function __construct(
        public string                    $name,
        public PlayerId                  $playerId,
        public string                    $playerColorClass,
        public bool                      $isPlayersTurn,
        public array                     $zeitsteine,
        public LebenszielPhaseDefinition $phaseDefinition,
        public LebenszielDefinition      $lebenszielDefinition,
        public MoneyAmount               $sumOfInvestments,
        public MoneyAmount               $annualExpensesForAllLoans,
        public ?JobCardDefinition        $job,
        public MoneyAmount               $gehalt,
        public MoneyAmount               $guthaben,
    )
    {
    }

}
