<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\QuitJobAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\QuitJob;

trait HasQuitJob
{

    public function quitJob(): void
    {
        $aktion = new QuitJobAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->coreGameLogic->handle($this->gameId, QuitJob::create($this->myself));
        $this->gameEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $this->broadcastNotify();

        $this->showBanner("Du hast deinen Job gekündigt.");
    }
}
