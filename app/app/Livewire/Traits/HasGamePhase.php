<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\EndSpielzugAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartSpielzugAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Illuminate\View\View;

trait HasGamePhase
{

    public bool $showItsYourTurnNotification = false;

    public function renderingHasGamePhase(): void
    {
        if (PreGameState::isInPreGamePhase($this->getGameEvents())) {
            return;
        }

        $this->showItsYourTurnNotification = false;

        // show notification if you are next active player
        if ($this->currentPlayerIsMyself() && new StartSpielzugAktion()->validate($this->myself, $this->getGameEvents())->canExecute) {
            $this->showItsYourTurnNotification = true;
        }
    }

    public function renderGamePhase(): View
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->getGameEvents());
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        return view('livewire.screens.ingame', [
            'year' => GamePhaseState::currentKonjunkturphasenYear($this->getGameEvents()),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
        ]);
    }


    public function canEndSpielzug(): AktionValidationResult
    {
        $aktion = new EndSpielzugAktion();
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

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
        $this->handleCommand(new EndSpielzug($this->myself));
        $this->broadcastNotify();
    }

    public function startSpielzug(): void
    {
        $this->handleCommand(new StartSpielzug($this->myself));
        $this->broadcastNotify();
    }
}
