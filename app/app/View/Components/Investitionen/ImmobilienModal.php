<?php

declare(strict_types=1);

namespace App\View\Components\Investitionen;

use App\Livewire\Dto\ImmoblienDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImmobilienModal extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $playerId,
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $immobilienCardIds = PileState::getFirstXCardsFromPile(
            $this->gameEvents,
            new PileId(
                CategoryId::INVESTITIONEN,
                PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $this->playerId)
            ),
            2
        );
        /** @var ImmobilienCardDefinition[] $immobilienCards */
        $immobilienCards = [];
        foreach ($immobilienCardIds as $immobilienCardId) {
            $immobilienCards[] = CardFinder::getInstance()->getCardById($immobilienCardId, ImmobilienCardDefinition::class);
        }

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

        return view('components.gameboard.investitionen.investitionen-immobilien-modal', [
            'immobilienCards' => $immobilienCards,
            'immobilienOwnedByPlayer' => $immobilienOwnedByPlayer,
        ]);
    }
}
