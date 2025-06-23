<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\DoMinijobAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DoMinijob;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;

trait HasMinijob
{
    public bool $isMinijobVisible = false;

    public function canDoMinijob(): bool
    {
        $aktion = new DoMinijobAktion();
        return $aktion->validate($this->myself, $this->gameEvents)->canExecute;
    }

    public function showMinijob(): void
    {
        $aktion = new DoMinijobAktion();
        $validationResult = $aktion->validate($this->myself,$this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, DoMinijob::create($this->myself)); //Minijob wird ausgeführt, Spieler-ID mit Spielerlogik übergeben
        $this->isMinijobVisible = true; //zeigt Minijob in UI an
        $this->broadcastNotify(); //Benachrichtigung
    }

    public function closeMinijob(): void
    {
        $this->isMinijobVisible = false;
    }

}
