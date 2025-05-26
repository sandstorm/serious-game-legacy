<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Illuminate\View\View;
use Illuminate\View\Component;

class KonjunkturphaseDetais extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameStream,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.konjunkturphase-details', [
            'konjunkturphase' => $this->getCurrentKonjunkturphaseDefinition(),
        ]);
    }

    public function getCurrentKonjunkturphaseDefinition(): ?KonjunkturphaseDefinition
    {
        $konjunkturphasenId = GamePhaseState::currentKonjunkturphasenId($this->gameStream);
        return KonjunkturphaseFinder::findKonjunkturphaseById(
            $konjunkturphasenId
        );
    }
}
