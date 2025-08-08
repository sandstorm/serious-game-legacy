<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class JobOffersModal extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId   $playerId,
        public GameEvents $gameEvents,
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $jobCardIds = PileState::getFirstXCardsFromPile(
            $this->gameEvents,
            new PileId(
                CategoryId::JOBS,
                PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $this->playerId)
            )
        );
        /** @var JobCardDefinition[] $jobs */
        $jobs = [];
        foreach ($jobCardIds as $jobId) {
            $jobs[] = CardFinder::getInstance()->getCardById($jobId, JobCardDefinition::class);
        }

        return view('components.gameboard.job-offers-modal', [
            'jobOffers' => $jobs,
            'playerId' => $this->playerId,
            'gameEvents' => $this->gameEvents,
        ]);
    }
}
