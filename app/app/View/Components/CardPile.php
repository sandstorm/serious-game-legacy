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
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $category,
        public string $cardPile,
        public GameEvents $gameStream,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        // TODO get next card when played/skipped
        $topCardIdForPile = PileState::topCardIdForPile($this->gameStream, PileId::from($this->cardPile));
        return view('components.gameboard.card-pile', [
            'category' => CategoryId::from($this->category),
            'card' => CardFinder::getInstance()->getCardById($topCardIdForPile),
        ]);
    }
}
