<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Helper\KompetenzenHelper;
use App\Livewire\Dto\GameboardInformationForKompetenzenOverview;
use App\Livewire\Dto\Kompetenzen;
use App\Livewire\Dto\KompetenzWithColor;
use App\Livewire\Dto\ZeitsteinWithColor;
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
        $currentLebenszielPhaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($this->gameEvents, $this->playerId);

        $categories = [
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::BILDUNG_UND_KARRIERE,
                kompetenzen: KompetenzenHelper::getKompetenzen(
                    PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                    PlayerState::getNameForPlayer($this->gameEvents, $this->playerId),
                    PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId),
                    $currentLebenszielPhaseDefinition->bildungsKompetenzSlots,
                    'gameboard.kompetenzen.kompetenz-icon-bildung',
                    CategoryId::BILDUNG_UND_KARRIERE
                ),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                kompetenzen: KompetenzenHelper::getKompetenzen(
                    PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                    PlayerState::getNameForPlayer($this->gameEvents, $this->playerId),
                    PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId),
                    $currentLebenszielPhaseDefinition->freizeitKompetenzSlots,
                    'gameboard.kompetenzen.kompetenz-icon-freizeit',
                    CategoryId::SOZIALES_UND_FREIZEIT
                ),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::JOBS,
                kompetenzen: $this->getKompentenzenBeruf(),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::INVESTITIONEN,
                kompetenzen: null,
            ),
        ];

        return view('components.gameboard.kompetenzenOverview.kompetenzen-overview', [
            'categories' => $categories,
            'investitionen' => $currentLebenszielPhaseDefinition->investitionen
        ]);
    }

    /**
     * @return Kompetenzen
     */
    private function getKompentenzenBeruf(): Kompetenzen
    {
        $playerHasJob = PlayerState::getJobForPlayer($this->gameEvents, $this->playerId);

        if ($playerHasJob === null) {
            $kompetenzen = [
                new KompetenzWithColor(
                    drawEmpty: true,
                    colorClass: '',
                    playerName: '',
                    iconComponentName: 'gameboard.kompetenzen.kompetenz-icon-beruf',
                )
            ];
        } else {
            $kompetenzen = [
                new KompetenzWithColor(
                    drawEmpty: false,
                    colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                    playerName: PlayerState::getNameForPlayer($this->gameEvents, $this->playerId),
                    iconComponentName: 'gameboard.kompetenzen.kompetenz-icon-beruf',
                ),
                new ZeitsteinWithColor(
                    drawEmpty: false,
                    colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                    playerName: PlayerState::getNameForPlayer($this->gameEvents, $this->playerId),
                ),
            ];
        }

        return new Kompetenzen(
            ariaLabel: $playerHasJob !== null ? 'Du hast einen Job (Ein Zeitstein ist dauerhaft gebunden)' : 'Du hast keinen Job',
            kompetenzen: $kompetenzen
        );
    }
}
