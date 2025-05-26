<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Pile\State\PileState;
use Domain\Definitions\Cards\CardFinder;
use Domain\Definitions\Cards\ValueObject\PileId;
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
    public function render(): View
    {
        // TODO get next card when played/skipped
        $topCardIdForPile = PileState::topCardIdForPile($this->gameStream, PileId::fromString($this->title));
        return view('components.gameboard.card-pile', [
            'title' => $this->title,
            'card' => CardFinder::getCardById($topCardIdForPile),
        ]);
    }
}
