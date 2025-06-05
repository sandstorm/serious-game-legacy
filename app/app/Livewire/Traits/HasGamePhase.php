<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Dto\GameboardInformationForCategory;
use App\Livewire\Dto\ZeitsteineForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;
use Illuminate\View\View;

trait HasGamePhase
{
    public function renderGamePhase(): View
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameStream);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        $placedZeitsteineBildung = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryEnum::BILDUNG);
        $placedZeitsteineFreizeit = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryEnum::FREIZEIT);

        $lebenszielForPlayer = PreGameState::lebenszielForPlayer($this->gameStream, $this->myself);

        // TODO DTO
        $categories = [
            new GameboardInformationForCategory(
                title: CategoryEnum::BILDUNG,
                kompetenzen: PlayerState::getBildungsKompetenzsteine($this->gameStream(), $this->myself),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->bildungsKompetenzSlots - PlayerState::getBildungsKompetenzsteine($this->gameStream(), $this->myself),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryEnum::BILDUNG) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineBildung),
                placedZeitsteine: $placedZeitsteineBildung,
                cardPile: PileId::BILDUNG_PHASE_1,
            ),
            new GameboardInformationForCategory(
                title: CategoryEnum::FREIZEIT,
                kompetenzen: PlayerState::getFreizeitKompetenzsteine($this->gameStream(), $this->myself),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->freizeitKompetenzSlots - PlayerState::getFreizeitKompetenzsteine($this->gameStream(), $this->myself),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryEnum::FREIZEIT) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineFreizeit),
                placedZeitsteine: $placedZeitsteineFreizeit,
                cardPile: PileId::FREIZEIT_PHASE_1,
            ),
            new GameboardInformationForCategory(
                title: CategoryEnum::INVESTITIONEN,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryEnum::INVESTITIONEN),
                placedZeitsteine: $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryEnum::INVESTITIONEN),
                cardPile: null
            ),
            new GameboardInformationForCategory(
                title: CategoryEnum::ERWEBSEINKOMMEN,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryEnum::ERWEBSEINKOMMEN),
                placedZeitsteine: $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryEnum::ERWEBSEINKOMMEN),
                cardPile: null
            ),
        ];

        return view('livewire.screens.ingame', [
            'currentYear' => GamePhaseState::currentKonjunkturphasenYear($this->gameStream),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
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
     * @param CategoryEnum $category
     * @return ZeitsteineForPlayer[]
     */
    private function getAllPlacedZeitsteineByPlayersInCategory(CategoryEnum $category): array
    {
        $players = PreGameState::playersWithNameAndLebensziel($this->gameStream());

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
     * @param CategoryEnum $category
     * @return ZeitsteineForPlayer
     */
    private function getPlacedZeitsteineForPlayerInCategory(PlayerId $playerId, CategoryEnum $category): ZeitsteineForPlayer
    {
        return new ZeitsteineForPlayer(
            PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($this->gameStream, $playerId, $category),
            $playerId,
        );
    }

    /**
     * @return void
     */
    public function spielzugAbschliessen(): void
    {
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->myself));
        $this->broadcastNotify();
    }

    /**
     * @param CategoryEnum $kompetenzbereich
     * @return int
     */
    public function getSlotsForKompetenzbereich(CategoryEnum $kompetenzbereich): int
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameStream);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        foreach ($konjunkturphasenDefinition->kompetenzbereiche as $kompetenzbereichDefinition) {
            if ($kompetenzbereichDefinition->name === $kompetenzbereich) {
                return $kompetenzbereichDefinition->kompetenzsteine;
            }
        }

        return 0;
    }

}
