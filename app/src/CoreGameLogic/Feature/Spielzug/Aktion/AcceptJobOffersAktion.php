<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;

class AcceptJobOffersAktion extends Aktion
{
    public function __construct(public CardId $jobId)
    {
        parent::__construct('request-job-offers', 'Jobs anzeigen');
    }

    private function getRequestedJobOffersForThisTurn(GameEvents $gameEvents): ?JobOffersWereRequested
    {
        $eventsThisTurn = AktionsCalculator::forStream($gameEvents)->getEventsThisTurn();
        return $eventsThisTurn->findLastOrNull(JobOffersWereRequested::class);
    }

    public function validate(PlayerId $player, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($player)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur einen Job annehmen, wenn du dran bist'
            );
        }
        $currentJobOffers = $this->getRequestedJobOffersForThisTurn($gameEvents);
        if ($currentJobOffers === null || !in_array($this->jobId->value, array_map(fn ($jobId) => $jobId->value, $currentJobOffers->jobs), true)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur einen Job annehmen, der dir vorgeschlagen wurde'
            );
        }

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($player, new ResourceChanges(zeitsteineChange: -1))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Zeitsteine, um den Job anzunehmen',
            );
        }

        /** @var JobCardDefinition $jobCard */
        $jobCard = CardFinder::getInstance()->getCardById($this->jobId);
        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordJobCard($player, $jobCard)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du erfüllst nicht die Voraussetzungen für diesen Job',
            );
        }

        return new AktionValidationResult(canExecute: true);
    }

    public function execute(PlayerId $player, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($player, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot Accept Job Offer: ' . $result->reason, 1749043636);
        }
        /** @var JobCardDefinition $job */
        $job = CardFinder::getInstance()->getCardById($this->jobId);
        return GameEventsToPersist::with(
            new JobOfferWasAccepted($player, $job->id, $job->gehalt),
        );
    }
}
