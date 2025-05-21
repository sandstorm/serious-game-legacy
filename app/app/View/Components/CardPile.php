<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\Definitions\Cards\CardFinder;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardPile extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $title,
        public GameEvents $gameStream,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // TODO get only the next card, not all.
        return view('components.gameboard.card-pile', [
            'title' => $this->title,
            'cards' => CardFinder::getCardsForPile(PileId::fromString($this->title)),
        ]);
    }
}
