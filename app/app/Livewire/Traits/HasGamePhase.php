<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\EndSpielzugAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Illuminate\View\View;

trait HasGamePhase
{
    public function renderGamePhase(): View
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameEvents);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        return view('livewire.screens.ingame', [
            'year' => GamePhaseState::currentKonjunkturphasenYear($this->gameEvents),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
        ]);
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
}
