<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Dto\GameboardInformationForCategory;
use App\Livewire\Dto\ZeitsteineForPlayer;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\EndSpielzugAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\View\View;

trait HasGamePhase
{
    public function renderGamePhase(): View
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameEvents);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        $placedZeitsteineBildung = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::BILDUNG_UND_KARRIERE);
        $placedZeitsteineFreizeit = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::SOZIALES_UND_FREIZEIT);
        $placedZeitsteineErwerbseinkommen = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::JOBS);
        $placedZeitsteineInvestitionen = $this->getAllPlacedZeitsteineByPlayersInCategory(CategoryId::INVESTITIONEN);

        $lebenszielForPlayer = PreGameState::lebenszielForPlayer($this->gameEvents, $this->myself);

        $categories = [
            new GameboardInformationForCategory(
                title: CategoryId::BILDUNG_UND_KARRIERE,
                kompetenzen: PlayerState::getBildungsKompetenzsteine($this->gameEvents(), $this->myself),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->bildungsKompetenzSlots - PlayerState::getBildungsKompetenzsteine($this->gameEvents(), $this->myself),
                availableZeitsteine: $this->getSlotsForKompetenzbereich(CategoryId::BILDUNG_UND_KARRIERE) - $this->getSumOfPlacedZeitsteineInCategory($placedZeitsteineBildung),
                placedZeitsteine: $placedZeitsteineBildung,
                cardPile: PileId::BILDUNG_PHASE_1,
            ),
            new GameboardInformationForCategory(
                title: CategoryId::SOZIALES_UND_FREIZEIT,
                kompetenzen: PlayerState::getFreizeitKompetenzsteine($this->gameEvents(), $this->myself),
                kompetenzenRequiredByPhase: $lebenszielForPlayer->definition->phaseDefinitions[0]->freizeitKompetenzSlots - PlayerState::getFreizeitKompetenzsteine($this->gameEvents(), $this->myself),
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

        return view('livewire.screens.ingame', [
            'currentYear' => GamePhaseState::currentKonjunkturphasenYear($this->gameEvents),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
            'categories' => $categories,
            'jobDefinition' => PlayerState::getJobForPlayer($this->gameEvents, $this->myself),
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
        $players = PreGameState::playersWithNameAndLebensziel($this->gameEvents());

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

    public function canEndSpielzug(): AktionValidationResult
    {
        $aktion = new EndSpielzugAktion();
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    /**
     * @return void
     */
    public function spielzugAbschliessen(): void
    {
        $validationResult = self::canEndSpielzug();
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->myself));
        $this->broadcastNotify();
    }

    /**
     * @param CategoryId $category
     * @return int
     */
    public function getSlotsForKompetenzbereich(CategoryId $category): int
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameEvents);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        return collect($konjunkturphasenDefinition->kompetenzbereiche)
            ->firstWhere('name', $category)->zeitsteinslots ?? 0;
    }

}
