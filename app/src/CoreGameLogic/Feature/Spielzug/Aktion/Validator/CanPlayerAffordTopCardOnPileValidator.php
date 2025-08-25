<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\PileId;

/**
 * Succeeds if the player has enough resources to activate the top card of the given pile
 */
final class CanPlayerAffordTopCardOnPileValidator extends AbstractValidator
{
    private PileId $pileId;

    public function __construct(PileId $pileId)
    {
        $this->pileId = $pileId;
    }

    private function getTotalCosts(PlayerId $playerId, GameEvents $gameEvents, CardDefinition $cardDefinition): ResourceChanges
    {
        $costToActivate = new ResourceChanges(
            zeitsteineChange: AktionsCalculator::forStream($gameEvents)->hasPlayerSkippedACardThisRound($playerId) ? 0 : -1
        );
        return $cardDefinition instanceof KategorieCardDefinition
            ? $costToActivate->accumulate(AktionsCalculator::forStream($gameEvents)->getModifiedResourceChangesForCard($cardDefinition))
            : $costToActivate;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $topCardOnPile = PileState::topCardIdForPile($gameEvents, $this->pileId);
        $cardDefinition = CardFinder::getInstance()->getCardById($topCardOnPile);

        if (!AktionsCalculator::forStream($gameEvents)->canPlayerAffordAction($playerId,
            $this->getTotalCosts($playerId, $gameEvents, $cardDefinition))) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Ressourcen um die Karte zu spielen',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
