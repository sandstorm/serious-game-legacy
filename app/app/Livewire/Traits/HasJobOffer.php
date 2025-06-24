<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\AcceptJobOffersAktion;
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
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function showJobOffer(): void
    {
        $validationResult = self::canRequestJobOffers();
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
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

    public function canAcceptJobOffer(CardId $cardId): AktionValidationResult
    {
        $aktion = new AcceptJobOffersAktion($cardId);
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    /**
     * @param string $cardIdString
     * @return void
     */
    public function applyForJob(string $cardIdString): void
    {
        $cardId = new CardId($cardIdString);
        $validationResult = self::canAcceptJobOffer($cardId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->coreGameLogic->handle(
            $this->gameId,
            AcceptJobOffer::create($this->myself, $cardId)
        );

        $this->jobOfferIsVisible = false;
        $this->broadcastNotify();
    }
}
