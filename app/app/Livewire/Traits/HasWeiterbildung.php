<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartWeiterbildungAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartWeiterbildung;

trait HasWeiterbildung
{
    public bool $isWeiterbildungVisible = false;

    public function canDoWeiterbildung(): bool
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
        $this->isWeiterbildungVisible = true;
        $this->broadcastNotify();
    }

    public function closeWeiterbildung(): void
    {
        $this->isWeiterbildungVisible = false;
    }

}
