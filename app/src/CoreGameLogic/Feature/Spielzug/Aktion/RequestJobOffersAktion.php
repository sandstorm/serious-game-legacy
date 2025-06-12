<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class RequestJobOffersAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('request-job-offers', 'Jobs anzeigen');
    }

    public function validate(PlayerId $player, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($player)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst nur Jobs anfragen, wenn du dran bist'
            );
        }

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($player, new ResourceChanges(zeitsteineChange: -2))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Zeitsteine, um dir Jobs anzeigen zu lassen',
            );
        }

        $hasFreeTimeSlots = GamePhaseState::hasFreeTimeSlotsForCategory($gameEvents, CategoryId::JOBS);
        if (!$hasFreeTimeSlots) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Es gibt keine freien Zeitsteine mehr.',
            );
        }

        return new AktionValidationResult(canExecute: true);
    }

    public function execute(PlayerId $player, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($player, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException("" . $result->reason, 1749043606);
        }
        $jobs = CardFinder::getInstance()->getThreeRandomJobs(PlayerState::getResourcesForPlayer($gameEvents, $player));
        return GameEventsToPersist::with(
            new JobOffersWereRequested($player, array_map(fn ($job) => $job->getId(), $jobs))
        );
    }
}
