<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

/**
 * Succeeds if the player owns the specified Immobilie.
 */
final class DoesPlayerOwnImmobilieValidator extends AbstractValidator
{
    private ImmobilieId $immobilieId;

    public function __construct(ImmobilieId $immobilieId) {
        $this->immobilieId = $immobilieId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $immobilieBoughtEvent = $gameEvents->findLastOrNullWhere(
            fn ($event) => $event instanceof PlayerHasBoughtImmobilie
                && $event->getPlayerId()->equals($playerId)
                && $event->getImmobilieId()->equals($this->immobilieId)
        );

        if ($immobilieBoughtEvent === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Diese Immobilie befindet sich nicht in deinem Besitz.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
