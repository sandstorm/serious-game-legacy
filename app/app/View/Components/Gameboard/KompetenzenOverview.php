<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Livewire\Dto\AbstractIconWithColor;
use App\Livewire\Dto\GameboardInformationForKompetenzenOverview;
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
        $lebenszielForPlayer = PlayerState::lebenszielForPlayer($this->gameEvents, $this->playerId);
        $currentLebenszielPhase = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($this->gameEvents, $this->playerId)->phase;

        $categories = [
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::BILDUNG_UND_KARRIERE,
                kompetenzen: $this->getKompetenzen(
                    PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId),
                    $lebenszielForPlayer->definition->phaseDefinitions[$currentLebenszielPhase - 1]->bildungsKompetenzSlots,
                    'gameboard.kompetenzen.kompetenz-icon-bildung'
                ),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                kompetenzen: $this->getKompetenzen(
                    PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId),
                    $lebenszielForPlayer->definition->phaseDefinitions[$currentLebenszielPhase - 1]->freizeitKompetenzSlots,
                    'gameboard.kompetenzen.kompetenz-icon-freizeit',
                ),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::JOBS,
                kompetenzen: $this->getKompentenzenBeruf(),
            ),
            new GameboardInformationForKompetenzenOverview(
                title: CategoryId::INVESTITIONEN,
                kompetenzen: [],
            ),
        ];

        return view('components.gameboard.kompetenzenOverview.kompetenzen-overview', [
            'categories' => $categories,
        ]);
    }

    /**
     * @param int $kompetenzen
     * @param int $requiredKompetenzen
     * @param string $iconComponentName
     * @return KompetenzWithColor[]
     */
    private function getKompetenzen(int $kompetenzen, int $requiredKompetenzen, string $iconComponentName): array
    {
        $kompetenzenArray = [];
        for ($i = 0; $i < $kompetenzen; $i++) {
            $kompetenzenArray[] = new KompetenzWithColor(
                drawEmpty: false,
                colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                playerName: PlayerState::nameForPlayer($this->gameEvents, $this->playerId),
                iconComponentName: $iconComponentName,
            );
        }

        for ($i = $kompetenzen; $i < $requiredKompetenzen - $kompetenzen; $i++) {
            $kompetenzenArray[] = new KompetenzWithColor(
                drawEmpty: true,
                colorClass: '',
                playerName: '',
                iconComponentName: $iconComponentName,
            );
        }

        return $kompetenzenArray;
    }

    /**
     * @return AbstractIconWithColor[]
     */
    private function getKompentenzenBeruf(): array
    {
        $playerHasJob = PlayerState::getJobForPlayer($this->gameEvents, $this->playerId);

        if ($playerHasJob === null) {
            return [
                new KompetenzWithColor(
                    drawEmpty: true,
                    colorClass: '',
                    playerName: '',
                    iconComponentName: 'gameboard.kompetenzen.kompetenz-icon-beruf',
                )
            ];
        } else {
            return [
                new KompetenzWithColor(
                    drawEmpty: false,
                    colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                    playerName: PlayerState::nameForPlayer($this->gameEvents, $this->playerId),
                    iconComponentName: 'gameboard.kompetenzen.kompetenz-icon-beruf',
                ),
                new ZeitsteinWithColor(
                    drawEmpty: false,
                    colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $this->playerId),
                    playerName: PlayerState::nameForPlayer($this->gameEvents, $this->playerId),
                ),
            ];
        }
    }
}
