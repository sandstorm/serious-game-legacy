<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

/**
 * Succeeds if it's the players turn.
 */
final class PlayerDoesNotYetHaveThisInsuranceValidator extends AbstractValidator
{
    public function __construct(private readonly InsuranceId $insuranceId)
    {
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $playerId, $this->insuranceId);

        if ($hasInsurance) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Versicherung wurde bereits abgeschlossen.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
