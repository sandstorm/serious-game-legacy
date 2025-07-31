<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Illuminate\View\Component;
use Illuminate\View\View;

class Lebensziel extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $playerId,
        public LebenszielDefinition $lebensziel,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.lebensziel.lebensziel', [
            'lebensziel' => $this->lebensziel
        ]);
    }

    public function getCostForPhaseChange(float $investitionen): MoneyAmount
    {
        return new MoneyAmount($investitionen * -1);
    }

}
