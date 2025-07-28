<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has skipped a card this turn.
 */
final class HasPlayerSkippedACardThisTurn extends AbstractValidator
{
    public function __construct()
    {
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (!AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound()) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Karte ablegen nur möglich, wenn vorher eine Karte übersprungen wurde',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
