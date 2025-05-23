<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\Definitions\Konjunkturzyklus\KonjunkturzyklusDefinition;
use Domain\Definitions\Konjunkturzyklus\KonjunkturzyklusFinder;
use Illuminate\View\View;
use Illuminate\View\Component;

class KonjunkturzyklusDetais extends Component
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
        return view('components.gameboard.konjunkturzyklus-details', [
            'konjunkturzyklus' => $this->getCurrentKonjunkturzyklusDefinition(),
        ]);
    }

    public function getCurrentKonjunkturzyklusDefinition(): ?KonjunkturzyklusDefinition
    {
        $konjunkturzyklus = GamePhaseState::currentKonjunkturzyklus($this->gameStream);
        return KonjunkturzyklusFinder::findKonjunkturZyklusById(
            $konjunkturzyklus->id
        );
    }
}
