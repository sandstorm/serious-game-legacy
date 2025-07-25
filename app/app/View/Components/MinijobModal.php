<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MinijobModal extends Component
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
        // For some reason the minijob can be null, between the time the player has done the minijob and the modal is opened.
        $minijobCardDefinition = PlayerState::getLastMinijobForPlayer(
            $this->gameEvents,
            $this->playerId
        );

        $miniJobResourceChanges = null;
        // add the cost (one zeitstein) for doing the minijob to the resource changes
        if ($minijobCardDefinition !== null) {
            $miniJobResourceChanges = $minijobCardDefinition->resourceChanges->accumulate(new ResourceChanges(zeitsteineChange: -1));
        }

        return view('components.gameboard.minijob-modal', [
            'minijob' => $minijobCardDefinition,
            'resourceChanges' => $miniJobResourceChanges,
        ]);
    }
}
