<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class JobOffersModal extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        /** @var JobOffersWereRequested $jobOfferWasRequested */
        $jobOfferWasRequested = $this->gameEvents->findLastOrNull(JobOffersWereRequested::class);

        /** @var JobCardDefinition[] $jobs */
        $jobs = [];
        if ($jobOfferWasRequested !== null) {
            $jobIds = $jobOfferWasRequested->jobs;
            foreach ($jobIds as $jobId) {
                $jobs[] = CardFinder::getInstance()->getCardById($jobId);
            }
        } else {
            // TODO close modal, show error?
        }

        return view('components.gameboard.job-offers-modal', [
            'jobOffers' => $jobs,
            'playerId' => $this->playerId,
        ]);
    }
}
