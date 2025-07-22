<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Livewire\Dto\GameboardInformationForKompetenzenOverview;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\View\Component;
use Illuminate\View\View;

class KompetenzenOverview extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $playerId,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $lebenszielForPlayer = PlayerState::lebenszielForPlayer($this->gameEvents, $this->playerId);
        $currentLebenszielPhase = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($this->gameEvents, $this->playerId)->phase;

        $categories = [
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::BILDUNG_UND_KARRIERE,
                kompetenzen: PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[$currentLebenszielPhase - 1]->bildungsKompetenzSlots - PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                kompetenzen: PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[$currentLebenszielPhase - 1]->freizeitKompetenzSlots - PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::JOBS,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::INVESTITIONEN,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
            ),
        ];

        return view('components.gameboard.kompetenzenOverview.kompetenzen-overview', [
            'categories' => $categories,
        ]);
    }
}
