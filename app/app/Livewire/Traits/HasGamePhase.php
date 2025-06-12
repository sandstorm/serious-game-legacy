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
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\View\View;

trait HasGamePhase
{
    public function renderGamePhase(): View
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameStream);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        $placedZeitsteineBildung = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::BILDUNG_UND_KARRIERE);
        $placedZeitsteineFreizeit = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::SOZIALES_UND_FREIZEIT);
        $placedZeitsteineErwerbseinkommen = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::JOBS);
        $placedZeitsteineInvestitionen = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::INVESTITIONEN);

        $lebenszielForPlayer = PreGameState::lebenszielForPlayer($this->gameStream, $this->myself);

        $categories = [
            new GameboardInformationForCategory(
                title: CategoryId::BILDUNG_UND_KARRIERE,
                kompetenzen: PlayerState::getBildungsKompetenzsteine($this->gameStream(), $this->myself),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->bildungsKompetenzSlots - PlayerState::getBildungsKompetenzsteine($this->gameStream(), $this->myself),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::BILDUNG_UND_KARRIERE) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineBildung),
                placedZeitsteine: $placedZeitsteineBildung,
                cardPile: PileId::BILDUNG_PHASE_1,
            ),
            new GameboardInformationForCategory(
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                kompetenzen: PlayerState::getFreizeitKompetenzsteine($this->gameStream(), $this->myself),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->freizeitKompetenzSlots - PlayerState::getFreizeitKompetenzsteine($this->gameStream(), $this->myself),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::SOZIALES_UND_FREIZEIT) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineFreizeit),
                placedZeitsteine: $placedZeitsteineFreizeit,
                cardPile: PileId::FREIZEIT_PHASE_1,
            ),
            new GameboardInformationForCategory(
                title: CategoryId::JOBS,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::JOBS) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineErwerbseinkommen),
                placedZeitsteine: $placedZeitsteineErwerbseinkommen,
            ),
            new GameboardInformationForCategory(
                title: CategoryId::INVESTITIONEN,
                kompetenzen: null,
                kompetenzenRequiredByPhase: null,
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::INVESTITIONEN) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineInvestitionen),
                placedZeitsteine: $placedZeitsteineInvestitionen,
            ),
        ];

        $currentJob = PlayerState::getJobForPlayer($this->gameStream, $this->myself);
        /** @var JobCardDefinition $jobCard */
        $jobCard = $currentJob !== null ? CardFinder::getInstance()->getCardById($currentJob->job) : null;

        return view('livewire.screens.ingame', [
            'currentYear' => GamePhaseState::currentKonjunkturphasenYear($this->gameStream),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
            'categories' => $categories,
            'jobDefinition' => $jobCard,
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
     * @param CategoryId $category
     * @return ZeitsteineForPlayer
     */
    private function getPlacedZeitsteineForPlayerInCategory(PlayerId $playerId, CategoryId $category): ZeitsteineForPlayer
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
     * @param CategoryId $category
     * @return int
     */
    public function getSlotsForKompetenzbereich(CategoryId $category): int
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameStream);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        return collect($konjunkturphasenDefinition->kompetenzbereiche)
            ->firstWhere('name', $category)->kompetenzsteine ?? 0;
    }

}
