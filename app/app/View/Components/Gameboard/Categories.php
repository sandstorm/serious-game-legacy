<?php

declare(strict_types=1);

namespace App\View\Components\Gameboard;

use App\Livewire\Dto\GameboardInformationForCategory;
use App\Livewire\Dto\ZeitsteinWithColor;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
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
        $categories = [
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories.categories-bildung',
                title: CategoryId::BILDUNG_UND_KARRIERE,
                zeitsteine: $this->getZeitsteineForCategory(CategoryId::BILDUNG_UND_KARRIERE),
                cardPile: PileId::BILDUNG_PHASE_1,
            ),
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories.categories-freizeit',
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                zeitsteine: $this->getZeitsteineForCategory(CategoryId::SOZIALES_UND_FREIZEIT),
                cardPile: PileId::FREIZEIT_PHASE_1,
            ),
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories-jobs',
                title: CategoryId::JOBS,
                zeitsteine: $this->getZeitsteineForCategory(CategoryId::JOBS),
            ),
            new GameboardInformationForCategory(
                componentName: 'gameboard.categories.categories-investitionen',
                title: CategoryId::INVESTITIONEN,
                zeitsteine: $this->getZeitsteineForCategory(CategoryId::INVESTITIONEN),
            ),
        ];

        return view('components.gameboard.categories.categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * @param CategoryId $category
     * @return ZeitsteinWithColor[]
     */
    private function getZeitsteineForCategory(CategoryId $category): array
    {
        $players = GamePhaseState::getOrderedPlayers($this->gameEvents);
        $availableSlots = $this->getSlotsForKompetenzbereich($category);

        $zeitsteine = [];

        foreach ($players as $player) {
            $amountOfZeitsteinePlayerPlaced = PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($this->gameEvents, $player, $category);

            for($i = 0; $i < $amountOfZeitsteinePlayerPlaced; $i++) {
                $zeitsteine[] = new ZeitsteinWithColor(
                    drawEmpty: false,
                    colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $player),
                    playerName: PlayerState::getNameForPlayer($this->gameEvents, $player),
                );
            }
        }

        // fill with empty zeitsteine for the remaining slots
        $remainingSlots = $availableSlots - count($zeitsteine);
        for ($i = 0; $i < $remainingSlots; $i++) {
            $zeitsteine[] = new ZeitsteinWithColor(drawEmpty: true);
        }

        return $zeitsteine;
    }

    /**
     * @param CategoryId $categoryId
     * @return int
     */
    private function getSlotsForKompetenzbereich(CategoryId $categoryId): int
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameEvents);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        $kompetenzbereich = $konjunkturphasenDefinition->getKompetenzbereichByCategory($categoryId);
        $playerIds = $this->gameEvents->findFirst(GameWasStarted::class)->playerOrdering;

        return $kompetenzbereich->zeitslots->getAmountOfZeitslotsForPlayer(count($playerIds));
    }
}
