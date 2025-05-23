<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\Definitions\Pile\Enum\PileEnum;
use Illuminate\View\View;

trait GameTrait
{
    public function renderGame(): View
    {
        $cardPiles = [
            PileEnum::BILDUNG_PHASE_1->value,
            PileEnum::FREIZEIT_PHASE_1->value,
            PileEnum::ERWERBSEINKOMMEN_PHASE_1->value,
        ];

        $konjunkturzyklus = GamePhaseState::currentKonjunkturzyklus($this->gameStream);

        return view('livewire.screens.ingame', [
            'cardPiles' => $cardPiles,
            'konjunkturzyklus' => $konjunkturzyklus,
        ]);
    }

    public function spielzugAbschliessen(): void
    {
        $this->coreGameLogic->handle($this->gameId, new SpielzugAbschliessen($this->myself));
        $this->broadcastNotify();
    }

}
