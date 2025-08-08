<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerAlreadyStartedSpielzugValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class StartSpielzugAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('start-spielzug', 'Spielzug starten');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerAlreadyStartedSpielzugValidator());
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot start spielzug: ' . $result->reason, 1754645425);
        }
        return GameEventsToPersist::with(
            new SpielzugWasStarted(
                playerId: $playerId,
                playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $playerId),
            )
        );
    }
}
