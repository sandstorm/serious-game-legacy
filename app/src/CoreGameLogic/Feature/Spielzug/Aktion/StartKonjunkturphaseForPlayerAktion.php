<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\PlayerId;

class StartKonjunkturphaseForPlayerAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('end-spielzug', 'Spielzug beenden');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        /** @var PlayerHasStartedKonjunkturphase $lastStartKonjunkturphaseEvent */
        $lastStartKonjunkturphaseEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof PlayerHasStartedKonjunkturphase && $event->playerId->equals($playerId));
        if (
            $lastStartKonjunkturphaseEvent !== null &&
            $lastStartKonjunkturphaseEvent->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast diese Konjunkturphase bereits gestartet'
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
            throw new \RuntimeException('Cannot start Konjunkturphase: ' . $result->reason, 1751373528);
        }
        return GameEventsToPersist::with(
            new PlayerHasStartedKonjunkturphase($playerId, KonjunkturphaseState::getCurrentYear($gameEvents))
        );
    }
}
