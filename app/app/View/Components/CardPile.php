<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\CardWithResourceChanges;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardPile extends Component
{
    private PileId $pileId;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $category,
        public string $cardPile,
        public GameEvents $gameEvents,
    ) {
        $this->pileId = PileId::fromString($this->cardPile);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $topCardIdForPile = PileState::topCardIdForPile($this->gameEvents, $this->pileId);
        /** @var CardWithResourceChanges & CardDefinition $cardDefinition */
        $cardDefinition = CardFinder::getInstance()->getCardById($topCardIdForPile);
        /**
         * WHY:
         * We cannot simply use the resourceChanges from the CardDefinition, since the current Konjunkturphase may
         * modify the money costs.
         */
        $resourceChanges = AktionsCalculator::forStream($this->gameEvents)->getModifiedResourceChangesForCard($cardDefinition);
        return view('components.gameboard.cardPile.card-pile', [
            'category' => CategoryId::from($this->category),
            'pileId' => $this->pileId,
            'card' => $cardDefinition,
            'resourceChanges' => $resourceChanges,
        ]);
    }
}
