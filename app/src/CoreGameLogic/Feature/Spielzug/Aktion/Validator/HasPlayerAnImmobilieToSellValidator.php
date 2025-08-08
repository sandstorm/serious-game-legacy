<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\ImmobilienType;

/**
 * Succeeds if the player has at least one immobilie of the same type to sell
 */
final class HasPlayerAnImmobilieToSellValidator extends AbstractValidator
{
    private ImmobilienType $immobilienType;

    public function __construct(ImmobilienType $immobilienType)
    {
        $this->immobilienType = $immobilienType;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $immobilien = PlayerState::getImmoblienOwnedByPlayer($gameEvents, $playerId);

        $hasImmobilieToSell = false;
        foreach ($immobilien as $immobilie) {
            if ($immobilie->getImmobilienTyp() === $this->immobilienType) {
                $hasImmobilieToSell = true;
                break;
            }
        }

        if (!$hasImmobilieToSell) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast keine Immobilie des Typs ' . $this->immobilienType->value . ' zum Verkaufen',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
