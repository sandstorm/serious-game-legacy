<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

/**
 * Succeeds if the player's current resources match or exceed the required resources.
 * TODO this might need some work; ResourceChanges have negative values for "required" resources; I have not tested this yet
 */
final class HasPlayerEnoughResourcesValidator extends AbstractValidator
{
    private ResourceChanges $resourceChanges;

    public function __construct(ResourceChanges $resourceChanges)
    {
        $this->resourceChanges = $resourceChanges;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction(
            $playerId,
            $this->resourceChanges
        )) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Ressourcen',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
