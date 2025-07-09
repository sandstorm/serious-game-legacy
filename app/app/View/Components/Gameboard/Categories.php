<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Livewire\Dto\GameboardInformationForCategory;
use App\Livewire\Dto\ZeitsteineForPlayer;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\View\Component;
use Illuminate\View\View;

class Categories extends Component
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
        $placedZeitsteineBildung = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::BILDUNG_UND_KARRIERE);
        $placedZeitsteineFreizeit = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::SOZIALES_UND_FREIZEIT);
        $placedZeitsteineErwerbseinkommen = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::JOBS);
        $placedZeitsteineInvestitionen = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::INVESTITIONEN);

        $lebenszielForPlayer = PreGameState::lebenszielForPlayer($this->gameEvents, $this->playerId);

        $categories = [
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories.categories-bildung',
                title: CategoryId::BILDUNG_UND_KARRIERE,
                kompetenzen: PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->bildungsKompetenzSlots - PlayerState::getBildungsKompetenzsteine($this->gameEvents, $this->playerId),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::BILDUNG_UND_KARRIERE) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineBildung),
                placedZeitsteine: $placedZeitsteineBildung,
                cardPile: PileId::BILDUNG_PHASE_1,
            ),
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories.categories-freizeit',
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                kompetenzen: PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->freizeitKompetenzSlots - PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $this->playerId),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::SOZIALES_UND_FREIZEIT) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineFreizeit),
                placedZeitsteine: $placedZeitsteineFreizeit,
                cardPile: PileId::FREIZEIT_PHASE_1,
            ),
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories-jobs',
                title: CategoryId::JOBS,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::JOBS) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineErwerbseinkommen),
                placedZeitsteine: $placedZeitsteineErwerbseinkommen,
            ),
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories.categories-investitionen',
                title: CategoryId::INVESTITIONEN,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::INVESTITIONEN) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineInvestitionen),
                placedZeitsteine: $placedZeitsteineInvestitionen,
            ),
        ];

        return view('components.gameboard.categories.categories', [
            'categories' => $categories,
        ]);
    }


    /**
     * @param ZeitsteineForPlayer[] $placedZeitsteine
     * @return int
     */
    private function getSumOfPlacedZeitsteineInCategory(array $placedZeitsteine): int
    {
        return array_reduce(
            $placedZeitsteine,
            fn (int $carry, ZeitsteineForPlayer $zeitsteineForPlayer) => $carry + $zeitsteineForPlayer->zeitsteine,
            0
        );
    }

    /**
     * returns all placed Zeitsteine for all players in a specific category.
     *
     * @param CategoryId $category
     * @return ZeitsteineForPlayer[]
     */
    private function getAllPlacedZeitsteineByPlayersInCategory(CategoryId $category): array
    {
        $players = PreGameState::playersWithNameAndLebensziel($this->gameEvents);

        $placedZeitsteine = [];
        foreach ($players as $player) {
            $placedZeitsteine[] = $this->getPlacedZeitsteineForPlayerInCategory($player->playerId, $category);
        }

        return $placedZeitsteine;
    }

    /**
     * returns the placed Zeitsteine for a player in a specific category.
     *
     * @param PlayerId $playerId
     * @param CategoryId $category
     * @return ZeitsteineForPlayer
     */
    private function getPlacedZeitsteineForPlayerInCategory(PlayerId $playerId, CategoryId $category): ZeitsteineForPlayer
    {
        return new ZeitsteineForPlayer(
            PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($this->gameEvents, $playerId, $category),
            $playerId,
        );
    }

    /**
     * @param CategoryId $category
     * @return int
     */
    private function getSlotsForKompetenzbereich(CategoryId $category): int
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameEvents);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        return collect($konjunkturphasenDefinition->kompetenzbereiche)
            ->firstWhere('name', $category)->zeitsteinslots ?? 0;
    }
}
