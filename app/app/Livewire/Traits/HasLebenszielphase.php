<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ChangeLebenszielphaseAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ChangeLebenszielphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;

trait HasLebenszielphase
{
    public bool $isChangeLebenszielphaseVisible = false;

    public function canChangeLebenszielphase(): bool
    {
        $aktion = new ChangeLebenszielphaseAktion();
        return $aktion->validate($this->myself, $this->gameEvents)->canExecute;
    }

    public function changeLebenszielphase(): void
    {
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

        /** @var LebenszielphaseWasChanged $event */
        $event = $this->gameEvents->findLast(LebenszielphaseWasChanged::class);

        $this->broadcastNotify();
        $this->showBanner("Du bist jetzt in Phase: " . $event->currentPhase->name, $event->getResourceChanges($this->myself));
    }
}
