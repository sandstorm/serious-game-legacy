<?php

declare(strict_types=1);

namespace App\View\Components\Investitionen;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\InvestitionenCardDefinition;
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
        $jobCardIds = PileState::getFirstXCardsFromPile(
            $this->gameEvents,
            new PileId(
                CategoryId::INVESTITIONEN,
                PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $this->playerId)
            ),
            2
        );
        /** @var InvestitionenCardDefinition[] $immobilien */
        $immobilien = [];
        foreach ($jobCardIds as $jobId) {
            $immobilien[] = CardFinder::getInstance()->getCardById($jobId, InvestitionenCardDefinition::class);
        }

        return view('components.gameboard.investitionen.investitionen-immobilien-modal', [
            'immobilien' => $immobilien,
            'immobilienOwned' => PlayerState::getImmoblienOwnedByPlayer($this->gameEvents, $this->playerId),
        ]);
    }
}
