<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ChangeLebenszielphaseAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;

trait HasLebenszielphase
{
    public bool $isChangeLebenszielphaseVisible = false;
    public bool $isFinishedGameModalVisible = false;

    /**
     * Rendering is triggered by Livewire when we use the broadcastNotify() method.
     * @return void
     */
    public function renderingHasLebenszielphase(): void
    {
        if (PreGameState::isInPreGamePhase($this->getGameEvents())) {
            // do not mount the if we are in pre-game phase
            return;
        }
        $this->isFinishedGameModalVisible = GamePhaseState::hasAnyPlayerFinishedLebensziel($this->getGameEvents());
    }

    public function canChangeLebenszielphase(): bool
    {
        $aktion = new ChangeLebenszielphaseAktion();
        return $aktion->validate($this->myself, $this->getGameEvents())->canExecute;
    }

    public function changeLebenszielphase(): void
    {
        $aktion = new ChangeLebenszielphaseAktion();
        $validationResult = $aktion->validate($this->myself, $this->getGameEvents());

        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->handleCommand(ChangeLebenszielphase::create($this->myself));
        $this->isChangeLebenszielphaseVisible = true;

        /** @var LebenszielphaseWasChanged $event */
        $event = $this->getGameEvents()->findLast(LebenszielphaseWasChanged::class);

        $this->broadcastNotify();
        if (GamePhaseState::hasAnyPlayerFinishedLebensziel($this->getGameEvents()) === false) {
            $this->showBanner("Du bist jetzt in Phase: " . $event->currentPhase->name, $event->getResourceChanges($this->myself));
        }
    }

    public function endGame(): void
    {
        redirect(route('game-play.index'));
    }
}
