<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\Definitions\Card\CardFinder;
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
        return view('components.gameboard.cardPile.card-pile', [
            'category' => CategoryId::from($this->category),
            'pileId' => $this->pileId,
            'card' => CardFinder::getInstance()->getCardById($topCardIdForPile),
        ]);
    }
}
