<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has either activated a card or put a card back on the pile this round,
 * after he skipped a card.
 */
final class HasPlayerActivatedACardOrPutACardBackOnPileValidator extends AbstractValidator
{
    public function __construct()
    {
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $aktionsCalculator = AktionsCalculator::forStream($gameEvents);

        if ($aktionsCalculator->hasPlayerSkippedACardThisRound($playerId) &&
            !$aktionsCalculator->hasPlayerPlayedACardOrPutOneBack($playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du musst die Karte entweder aktivieren oder zurück auf den Stapel legen.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
