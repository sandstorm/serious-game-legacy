<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\DecoratedEvent;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\KonjunkturphaseCommandHandler;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasKonjunkturphaseEndedValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerAPositiveBalanceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerCompletedMoneySheetValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerWasMarkedAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\PlayerId;

class MarkPlayerAsReadyForKonjunkturphaseChangeAktion extends Aktion
{

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new HasKonjunkturphaseEndedValidator();
        $validatorChain
            ->setNext(new HasPlayerCompletedMoneySheetValidator())
            ->setNext(new HasPlayerAPositiveBalanceValidator());
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot mark player as ready: ' . $result->reason, 1756798041);
        }

        $eventsToPersist = GameEventsToPersist::with(
            new PlayerWasMarkedAsReadyForKonjunkturphaseChange(
                playerId: $playerId,
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
            ),

        );

        // We want to check if all players are ready now. Therefore we need all game events AND the new events that have
        // not yet been persisted. If there are any decorated events we need to unpack them first (we only need the
        // inner event for the check).
        $gameEventsAndEventsToPersist = GameEvents::fromArray([
            ...$gameEvents,
            ...array_map(fn($event) => $event instanceof DecoratedEvent ? $event->innerEvent : $event,
                $eventsToPersist->events)
        ]);
        if (KonjunkturphaseState::areAllPlayersMarkedAsReadyForKonjunkturphaseChange($gameEventsAndEventsToPersist)) {
            // If all players are ready -> change Konjunkturphase
            $konjunkturphaseCommandHandler = new KonjunkturphaseCommandHandler();
            return $eventsToPersist->withAppendedEvents(
                // TODO direct call of command handler
                ...$konjunkturphaseCommandHandler->handleChangeKonjunkturphase(ChangeKonjunkturphase::create(),
                $gameEvents)->events
            );
        }

        return $eventsToPersist;
    }
}
