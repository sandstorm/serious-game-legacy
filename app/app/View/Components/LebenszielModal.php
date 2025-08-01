<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class LebenszielModal extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.lebensziel-modal', [
            'lebenszielDefinition' => PlayerState::getLebenszielDefinitionForPlayer($this->gameEvents, $this->playerId)
        ]);
    }
}
