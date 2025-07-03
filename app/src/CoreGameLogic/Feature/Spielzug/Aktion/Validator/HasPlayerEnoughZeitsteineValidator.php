<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

/**
 * Succeeds if the player as at least the required number of Zeitsteine.
 */
final class HasPlayerEnoughZeitsteineValidator extends AbstractValidator
{
    private int $requiredNumberOfZeitsteine;

    public function __construct(int $requiredNumberOfZeitsteine)
    {
        $this->requiredNumberOfZeitsteine = $requiredNumberOfZeitsteine;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction(
            $playerId,
            new ResourceChanges(zeitsteineChange: -1 * $this->requiredNumberOfZeitsteine)
        )) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Zeitsteine',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
