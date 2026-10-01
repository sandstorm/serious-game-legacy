<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player owns the specified Immobilie.
 */
final class DoesPlayerOwnImmobilieValidator extends AbstractValidator
{
    private ImmobilieId $immobilieId;

    public function __construct(ImmobilieId $immobilieId)
    {
        $this->immobilieId = $immobilieId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        // only Immobilien that have not been sold yet
        $ownsImmobilie = array_any(
            PlayerState::getImmoblienOwnedByPlayer($gameEvents, $playerId),
            fn (PlayerHasBoughtImmobilie $event) => $event->getImmobilieId()->equals($this->immobilieId)
        );

        if (!$ownsImmobilie) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Diese Immobilie befindet sich nicht in deinem Besitz.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
