<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ChangeLebenszielphaseAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\FinishGameAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Command\FinishGame;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasFinishedTheGame;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;

trait HasLebenszielphase
{
    public bool $isChangeLebenszielphaseVisible = false;

    public function canChangeLebenszielphase(): bool
    {
        $currentLebenszielphase = PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $this->myself)->value;

        if ($currentLebenszielphase === 3) {
            $aktion = new FinishGameAktion();
        } else {
            $aktion = new ChangeLebenszielphaseAktion();
        }
        return $aktion->validate($this->myself, $this->gameEvents)->canExecute;
    }

    public function changeLebenszielphase(): void
    {
        $currentLebenszielphase = PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $this->myself)->value;

        if ($currentLebenszielphase === 3) {
            $aktion = new FinishGameAktion();
            $validationResult = $aktion->validate($this->myself, $this->gameEvents);

            if (!$validationResult->canExecute) {
                $this->showNotification(
                    $validationResult->reason,
                    NotificationTypeEnum::ERROR
                );
                return;
            }

            $this->coreGameLogic->handle($this->gameId, FinishGame::create($this->myself));
            $this->isChangeLebenszielphaseVisible = true;
            $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

            /** @var PlayerHasFinishedTheGame $event*/
            $event = $this->gameEvents->findLast(PlayerHasFinishedTheGame::class);

            $this->broadcastNotify();
            $this->showBanner("Du hast dein Lebensziel erreicht.");
        }
        else {
            $aktion = new ChangeLebenszielphaseAktion();
            $validationResult = $aktion->validate($this->myself, $this->gameEvents);

            if (!$validationResult->canExecute) {
                $this->showNotification(
                    $validationResult->reason,
                    NotificationTypeEnum::ERROR
                );
                return;
            }

            $this->coreGameLogic->handle($this->gameId, ChangeLebenszielphase::create($this->myself));
            $this->isChangeLebenszielphaseVisible = true;
            $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);

            /** @var LebenszielphaseWasChanged $event */
            $event = $this->gameEvents->findLast(LebenszielphaseWasChanged::class);

            $this->broadcastNotify();
            $this->showBanner("Du bist jetzt in Phase: " . $event->currentPhase->name, $event->getResourceChanges($this->myself));
        }
    }
}
