<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class EndSpielzugAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('end-spielzug', 'Spielzug beenden');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du bist gerade nicht dran'
            );
        }

        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class);
        if ($eventsThisTurn === null) {
            $eventsThisTurn = $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
        }
        if (
            $eventsThisTurn->findLastOrNull(ZeitsteinAktion::class) === null
            && PlayerState::getZeitsteineForPlayer($gameEvents, $playerId) !== 0
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du musst erst einen Zeitstein für eine Aktion ausgeben'
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
            throw new \RuntimeException('Cannot end spielzug: ' . $result->reason, 1748946243);
        }
        return GameEventsToPersist::with(
            new SpielzugWasEnded($playerId)
        );
    }
}
