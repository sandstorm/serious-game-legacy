<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

/**
 * Succeeds if the given Immobilie is currently in the first two cards of the Immobilien pile (for the player's phase).
 */
final class IsImmobilieCurrentlyAvailableValidator extends AbstractValidator
{
    private CardId $cardId;

    public function __construct(CardId $cardId)
    {
        $this->cardId = $cardId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $currentlyAvailableImmobilien = PileState::getFirstXCardsFromPile(
            gameEvents: $gameEvents,
            pileId: new PileId(CategoryId::INVESTITIONEN, PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)),
            amount: 2
        );
        if (!in_array($this->cardId->value, array_map(fn ($jobId) => $jobId->value, $currentlyAvailableImmobilien), true)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Diese Immobilie steht aktuell nicht zum Verkauf'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
