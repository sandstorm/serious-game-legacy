<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerDoneAtLeastOneZeitsteinaktionThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\NoPlayerNeedsToSellStocksValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class EndSpielzugAktion extends Aktion
{
    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new HasPlayerDoneAtLeastOneZeitsteinaktionThisTurnValidator())
            ->setNext(new NoPlayerNeedsToSellStocksValidator());

        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot end spielzug: ' . $result->reason, 1748946243);
        }
        return GameEventsToPersist::with(
            new SpielzugWasEnded(
                playerId: $playerId,
                playerTurn: PlayerState::getCurrentTurnForPlayer($gameEvents, $playerId),
            )
        );
    }
}
