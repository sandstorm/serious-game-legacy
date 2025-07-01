<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\DecoratedEvent;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\KonjunkturphaseCommandHandler;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Command\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerWasMarkedAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;

class MarkPlayerAsReadyForKonjunkturphaseChangeAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('todo', 'todo');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $lastCompletedMoneysheetEvent = $gameEvents->findLastOrNullWhere(function ($event) use ($playerId, $gameEvents) {
            return $event instanceof PlayerWasMarkedAsReadyForKonjunkturphaseChange &&
                $event->playerId->equals($playerId) &&
                KonjunkturphaseState::getCurrentYear($gameEvents)->equals($event->year);
        });

        if ($lastCompletedMoneysheetEvent === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du musst erst das Money Sheet korrekt ausfüllen"
            );
        }
        return new AktionValidationResult(
            canExecute: true,
        );
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot mark player as ready: ' . $result->reason, 1751373528);
        }

        $eventsToPersist = GameEventsToPersist::with(
            new PlayerWasMarkedAsReadyForKonjunkturphaseChange(
                playerId: $playerId,
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
            ),

        );
        if (KonjunkturphaseState::areAllPlayersMarkedAsReadyForKonjunkturphaseChange(GameEvents::fromArray([
            ...$gameEvents,
            ...array_map(fn($event) => $event instanceof DecoratedEvent ? $event->innerEvent : $event,
                $eventsToPersist->events)
        ]))) {
            $eventsToPersist->withAppendedEvents(
                // TODO find a better way
                ...(new KonjunkturphaseCommandHandler())->handleChangeKonjunkturphase(ChangeKonjunkturphase::create(),
                $gameEvents)->events
            );
        }
        return $eventsToPersist;
    }
}
