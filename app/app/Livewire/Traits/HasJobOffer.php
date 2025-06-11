<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Spielzug\Aktion\RequestJobOffersAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\Definitions\Card\ValueObject\CardId;

trait HasJobOffer
{
    public bool $jobOfferIsVisible = false;

    public function canRequestJobOffers(): AktionValidationResult
    {
        $aktion = new RequestJobOffersAktion();
        return $aktion->validate($this->myself, $this->gameStream);
    }

    public function showJobOffer(): void
    {
        $validationResult = self::canRequestJobOffers();
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationType::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, RequestJobOffers::create($this->myself));
        $this->jobOfferIsVisible = true;
        $this->broadcastNotify();
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
