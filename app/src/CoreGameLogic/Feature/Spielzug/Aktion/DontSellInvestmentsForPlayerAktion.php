<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasAnotherPlayerInvestedThisTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasNotSoldInvestments;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

class DontSellInvestmentsForPlayerAktion extends Aktion
{
    public function __construct(
        private readonly InvestmentId $investmentId,
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new HasAnotherPlayerInvestedThisTurnValidator($this->investmentId);
        return $validationChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('' . $result->reason, 1754482137);
        }

        return GameEventsToPersist::with(
            new PlayerHasNotSoldInvestments(
                playerId: $playerId,
            )
        );
    }
}
