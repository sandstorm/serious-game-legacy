<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\ImmobilieWasBoughtForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

/**
 * Succeeds if the player has at least one immobilie to sell.
 */
final class HasPlayerImmobilieToSellValidator extends AbstractValidator
{
    private CardId $cardId;

    public function __construct(CardId $cardId) {
        $this->cardId = $cardId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $immobilieBoughtEvent = $gameEvents->findLastOrNullWhere(
            fn ($event) => $event instanceof ImmobilieWasBoughtForPlayer
                && $event->playerId->equals($playerId)
                && $event->cardId->equals($this->cardId)
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
