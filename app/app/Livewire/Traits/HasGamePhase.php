<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

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

        $players = PreGameState::playersWithNameAndLebensziel($this->gameStream());
        $usedZeitsteineBildung = [];
        $sumOfUsedZeitsteineBildung = 0;
        foreach ($players as $player) {
            $zeitsteine = $this->getPlacedZeitsteineForPlayerInCategory($player->playerId, CategoryEnum::BILDUNG);
            $usedZeitsteineBildung[] = $zeitsteine;
            $sumOfUsedZeitsteineBildung += $zeitsteine['zeitsteine'];
        }

        $usedZeitsteineFreizeit = [];
        $sumOfUsedZeitsteineFreizeit = 0;
        foreach ($players as $player) {
            $zeitsteine = $this->getPlacedZeitsteineForPlayerInCategory($player->playerId, CategoryEnum::FREIZEIT);
            $usedZeitsteineFreizeit[] = $zeitsteine;
            $sumOfUsedZeitsteineFreizeit += $zeitsteine['zeitsteine'];
        }

        $lebenszielForPlayer = PreGameState::lebenszielForPlayer($this->gameStream, $this->myself);

        // TODO DTO
        $categories = [
            [
                'title' => CategoryEnum::BILDUNG->value,
                'kompetenzen'=> PlayerState::getBildungsKompetenzsteine($this->gameStream(), $this->myself),
                // TODO get current phase of the player
                'kompetenzenRequiredByPhase' => $lebenszielForPlayer->definition->phaseDefinitions[0]->bildungsKompetenzSlots - PlayerState::getBildungsKompetenzsteine($this->gameStream(), $this->myself),
                'availableZeitsteine' => $this->getSlotsForKompetenzbereich(CategoryEnum::BILDUNG) - $sumOfUsedZeitsteineBildung,
                'placedZeitsteine' => $usedZeitsteineBildung,
                'cardPile' => PileId::BILDUNG_PHASE_1->value,
            ],
            [
                'title' => CategoryEnum::FREIZEIT->value,
                'kompetenzen'=> PlayerState::getFreizeitKompetenzsteine($this->gameStream(), $this->myself),
                'kompetenzenRequiredByPhase' => $lebenszielForPlayer->definition->phaseDefinitions[0]->freizeitKompetenzSlots - PlayerState::getFreizeitKompetenzsteine($this->gameStream(), $this->myself),
                'availableZeitsteine' => $this->getSlotsForKompetenzbereich(CategoryEnum::FREIZEIT) - $sumOfUsedZeitsteineFreizeit,
                'placedZeitsteine' => $usedZeitsteineFreizeit,
                'cardPile' => PileId::FREIZEIT_PHASE_1->value,
            ],
            [
                'title' => CategoryEnum::INVESTITIONEN->value,
                'kompetenzen'=> null,
                'kompetenzenRequiredByPhase'=> null,
                'availableZeitsteine' => $this->getSlotsForKompetenzbereich(CategoryEnum::INVESTITIONEN),
                'placedZeitsteine' => [],
                'cardPile' => null,
            ],
            [
                'title' => CategoryEnum::ERWEBSEINKOMMEN->value,
                'kompetenzen'=> null,
                'kompetenzenRequiredByPhase'=> null,
                'availableZeitsteine' => $this->getSlotsForKompetenzbereich(CategoryEnum::ERWEBSEINKOMMEN),
                'placedZeitsteine' => [],
                'cardPile' => null,
            ],

        ];

        return view('livewire.screens.ingame', [
            'currentYear' => GamePhaseState::currentKonjunkturphasenYear($this->gameStream),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
            'categories' => $categories,
        ]);
    }

    /**
     * @param PlayerId $playerId
     * @param CategoryEnum $category
     * @return array<string, mixed>
     */
    private function getPlacedZeitsteineForPlayerInCategory(PlayerId $playerId, CategoryEnum $category): array
    {
        // TODO DTO
        return [
            'zeitsteine' => PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($this->gameStream, $playerId, $category),
            'playerId' => $playerId,
        ];
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
