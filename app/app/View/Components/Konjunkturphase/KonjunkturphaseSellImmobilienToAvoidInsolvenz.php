<?php

declare(strict_types=1);

namespace App\View\Components\Konjunkturphase;

use App\Livewire\Dto\ImmoblienDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Illuminate\View\Component;
use Illuminate\View\View;

class KonjunkturphaseSellImmobilienToAvoidInsolvenz extends Component
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
    public function render(): View
    {
        $immobilienOwnedByPlayer = [];

        foreach(PlayerState::getImmoblienOwnedByPlayer($this->gameEvents, $this->playerId) as $immobilie) {
            $immoblienDefinition = CardFinder::getInstance()->getCardById($immobilie->getCardId(), ImmobilienCardDefinition::class);
            $immobilienOwnedByPlayer[] = new ImmoblienDto(
                title: $immoblienDefinition->getTitle(),
                immobilieId: $immobilie->getImmobilieId(),
                purchasePrice: $immoblienDefinition->getPurchasePrice(),
                annualRent: $immoblienDefinition->getAnnualRent(),
            );
        }

        return view('components.konjunkturphase.konjunkturphase-sell-immobilien-to-avoid-insolvenz-modal', [
            'immobilienOwnedByPlayer' => $immobilienOwnedByPlayer
        ]);
    }

}
