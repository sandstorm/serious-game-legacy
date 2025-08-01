<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Helper\KompetenzenHelper;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;
use Illuminate\View\Component;
use Illuminate\View\View;

class LebenszielKompetenzen extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $playerId,
        public LebenszielPhaseDefinition $lebenszielPhase,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $currentLebenszielPhaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($this->gameEvents, $this->playerId);
        $bildungsKompetenzen = $currentLebenszielPhaseDefinition->lebenszielPhaseId->value === $this->lebenszielPhase->lebenszielPhaseId->value ? PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId) : 0;
        $freizeitKompetenzen = $currentLebenszielPhaseDefinition->lebenszielPhaseId->value === $this->lebenszielPhase->lebenszielPhaseId->value ? PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId) : 0;

        return view('components.gameboard.lebensziel.lebensziel-kompetenzen', [
            'bildungsKompetenzen' => KompetenzenHelper::getKompetenzen(
                PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                PlayerState::getNameForPlayer($this->gameEvents, $this->playerId),
                $bildungsKompetenzen,
                $this->lebenszielPhase->bildungsKompetenzSlots,
                'gameboard.kompetenzen.kompetenz-icon-bildung'
            ),
            'freizeitKompetenzen' => KompetenzenHelper::getKompetenzen(
                PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                PlayerState::getNameForPlayer($this->gameEvents, $this->playerId),
                $freizeitKompetenzen,
                $this->lebenszielPhase->freizeitKompetenzSlots,
                'gameboard.kompetenzen.kompetenz-icon-freizeit',
            )
        ]);
    }
}
