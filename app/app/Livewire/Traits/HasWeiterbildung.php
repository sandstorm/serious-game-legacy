<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\WeiterbildungForm;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartWeiterbildungAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SubmitAnswerForWeiterbildung;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\ValueObject\AnswerId;

trait HasWeiterbildung
{
    public bool $isWeiterbildungVisible = false;
    public WeiterbildungForm $weiterbildungForm;

    public function mountHasWeiterbildung(): void
    {
        if (PreGameState::isInPreGamePhase($this->gameEvents)) {
            // do not mount the if we are in pre-game phase
            return;
        }
        $this->isWeiterbildungVisible = false;

        if ($this->hasPlayerStartedWeiterbildungWithoutAnswering()) {
            $this->isWeiterbildungVisible = true;
        }
    }

    private function hasPlayerStartedWeiterbildungWithoutAnswering(): bool
    {
        $gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $currentWeiterbildungsCard = PlayerState::getLastWeiterbildungCardDefinitionForPlayer($gameEvents, $this->myself);
        if ($currentWeiterbildungsCard === null) {
            return false;
        }

        $submittedAnswerEvent = PlayerState::getSubmittedAnswerForLatestWeiterbildungThisTurn(
            $gameEvents,
            $this->myself,
            $currentWeiterbildungsCard->getId()
        );

        return $submittedAnswerEvent === null;
    }

    public function canStartWeiterbildung(): bool
    {
        $aktion = new StartWeiterbildungAktion();
        return $aktion->validate($this->myself, $this->gameEvents)->canExecute;
    }

    public function showWeiterbildung(): void
    {
        $aktion = new StartWeiterbildungAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, StartWeiterbildung::create($this->myself));
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

        $this->isWeiterbildungVisible = true;
        $this->broadcastNotify();
    }

    public function closeWeiterbildung(): void
    {
        // prevent closing the modal if the player has started a Weiterbildung without answering
        if ($this->hasPlayerStartedWeiterbildungWithoutAnswering()) {
            return;
        }
        $this->isWeiterbildungVisible = false;
    }

    public function submitAnswerForWeiterbildung(): void
    {
        $selectedAnswerId = new AnswerId($this->weiterbildungForm->answer);
        $this->coreGameLogic->handle($this->gameId, SubmitAnswerForWeiterbildung::create(
            $this->myself,
            $selectedAnswerId
        ));

        $this->broadcastNotify();
        $this->isWeiterbildungVisible = true;
    }
}
