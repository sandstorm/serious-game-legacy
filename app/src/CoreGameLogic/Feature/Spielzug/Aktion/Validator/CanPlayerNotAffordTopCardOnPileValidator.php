<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\ValueObject\PileId;

/**
 * Succeeds if the player has not enough resources to activate the top card of the given pile
 */
final class CanPlayerNotAffordTopCardOnPileValidator extends AbstractValidator
{
    private PileId $pileId;

    public function __construct(PileId $pileId)
    {
        $this->pileId = $pileId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        $cardDefinition = CardFinder::getInstance()->getCardById($topCardOnPile);
        $costs = AktionsCalculator::forStream($gameEvents)->getTotalCostsForActivatingCard($playerId, $cardDefinition);

        if (AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($playerId, $costs)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast genug Ressourcen um die Karte zu spielen, du darfs sie nicht ablegen.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
