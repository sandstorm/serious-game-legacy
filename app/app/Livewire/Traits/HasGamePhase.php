<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\Definitions\Pile\Enum\PileEnum;
use Illuminate\View\View;

trait HasGamePhase
{
    public function renderGamePhase(): View
    {
        $cardPiles = [
            PileEnum::BILDUNG_PHASE_1->value,
            PileEnum::FREIZEIT_PHASE_1->value,
            PileEnum::ERWERBSEINKOMMEN_PHASE_1->value,
        ];

        $konjunkturphase = GamePhaseState::currentKonjunkturphase($this->gameStream);

        return view('livewire.screens.ingame', [
            'cardPiles' => $cardPiles,
            'konjunkturphase' => $konjunkturphase,
        ]);
    }

    public function spielzugAbschliessen(): void
    {
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->myself));
        $this->broadcastNotify();
    }

}
