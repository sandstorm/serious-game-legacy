<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\EreignisCardDefinition;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EreignisModal extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        /** @var EreignisCardDefinition $ereignisCardDefinition */
        $ereignisCardDefinition = CardFinder::getInstance()
            ->getCardById(
                $this->gameEvents->findLast(EreignisWasTriggered::class)->ereignisCardId,
                EreignisCardDefinition::class
            );

        return view('components.gameboard.ereignis-modal', [
            'ereignisCard' => $ereignisCardDefinition,
        ]);
    }
}
