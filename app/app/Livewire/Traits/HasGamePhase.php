<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Illuminate\View\View;

trait HasGamePhase
{
    public function renderGamePhase(): View
    {
        $cardPiles = [
            PileId::BILDUNG_PHASE_1->value,
            PileId::FREIZEIT_PHASE_1->value,
        ];

        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameStream);
        $konjunkturphasenDefinition = KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );

        return view('livewire.screens.ingame', [
            'cardPiles' => $cardPiles,
            'currentYear' => GamePhaseState::currentKonjunkturphasenYear($this->gameStream),
            'konjunkturphasenDefinition' => $konjunkturphasenDefinition,
            'guthaben' => PlayerState::getGuthabenForPlayer($this->gameStream, $this->myself)
        ]);
    }

    public function spielzugAbschliessen(): void
    {
        $this->coreGameLogic->handle($this->gameId, new EndSpielzug($this->myself));
        $this->broadcastNotify();
    }

}
