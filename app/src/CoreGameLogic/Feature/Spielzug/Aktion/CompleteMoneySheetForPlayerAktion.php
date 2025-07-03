<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\PlayerId;

class CompleteMoneySheetForPlayerAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('complete-money-sheet', 'Money Sheet vervollständigen');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        /** @var KonjunkturphaseHasEnded $lastKonjunkturphaseHasEndedEvent */
        $lastKonjunkturphaseHasEndedEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof KonjunkturphaseHasEnded && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents)));
        if ($lastKonjunkturphaseHasEndedEvent === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Die aktuelle Konjunkturphase ist noch nicht zu Ende'
            );
        }
        if (MoneySheetState::doesMoneySheetRequirePlayerAction($gameEvents, $playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du musst erst dein Money Sheet korrekt ausfüllen'
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
            throw new \RuntimeException('Cannot complete money sheet: ' . $result->reason, 1751375431);
        }
        return GameEventsToPersist::with(
            new PlayerHasCompletedMoneysheetForCurrentKonjunkturphase($playerId, KonjunkturphaseState::getCurrentYear($gameEvents))
        );
    }
}
