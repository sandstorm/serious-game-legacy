<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\Definitions\Card\ValueObject\CardId;

trait HasJobOffer
{
    public bool $jobOfferIsVisible = false;
    public string $requestJobOffersErrorMessage = '';

    public function showJobOffer(): void
    {
        try {
            $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->myself));
            $this->jobOfferIsVisible = true;
            $this->broadcastNotify();
        } catch (\Throwable $e) {
            $this->requestJobOffersErrorMessage = $e->getMessage();
            return;
        }
    }

    public function closeJobOffer(): void
    {
        $this->jobOfferIsVisible = false;
    }

    /**
     * @param string $cardId
     * @return void
     */
    public function applyForJob(string $cardId): void
    {
        $this->coreGameLogic->handle(
            $this->gameId,
            AcceptJobOffer::create($this->myself, new CardId($cardId))
        );

        $this->jobOfferIsVisible = false;
        $this->broadcastNotify();
    }
}
