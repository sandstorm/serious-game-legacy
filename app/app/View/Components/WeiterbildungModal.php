<?php
declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WeiterbildungModal extends Component
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
    public function render(): View|Closure|string
    {
        $weiterbildungCardDefinition = PlayerState::getLastWeiterbildungForPlayer(
            $this->gameEvents,
            $this->playerId
        );

        return view('components.gameboard.weiterbildung-modal', [
            'weiterbildung' => $weiterbildungCardDefinition,
            'playerId' => $this->playerId,
        ]);
    }
}
