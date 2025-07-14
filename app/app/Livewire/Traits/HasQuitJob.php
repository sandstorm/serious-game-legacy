<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Spielzug\Aktion\QuitJobAktion;

trait HasQuitJob
{
    public bool $quitJobIsVisible = false;

    public function quitJob(): bool
    {
        $aktion = new QuitJobAktion();
        return $aktion->validate($this->myself, $this->gameEvents)->canExecute;
    }
}
