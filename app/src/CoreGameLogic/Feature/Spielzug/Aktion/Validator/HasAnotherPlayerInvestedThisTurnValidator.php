<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasSoldInvestment;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if another player has bought stocks of the same type this turn.
 */
final class HasAnotherPlayerInvestedThisTurnValidator extends AbstractValidator
{
    private InvestmentId $investmentId;

    public function __construct(
        InvestmentId $investmentId,
    ) {
        $this->investmentId = $investmentId;
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);

        $investmentEventThisTurn = $eventsThisTurn->findLastOrNullWhere(
            fn ($event) => ($event instanceof PlayerHasBoughtInvestment || $event instanceof PlayerHasSoldInvestment)
                && !$event->getPlayerId()->equals($playerId)
                && $event->getInvestmentId() === $this->investmentId
        );

        if ($investmentEventThisTurn === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Ein anderer Spieler muss Investitionen der gleichen Art gekauft oder verkauft haben, bevor du welche verkaufen kannst.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
