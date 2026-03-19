<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;

final class HasPlayerNoOpenLoansWhenFinishingValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $currentPhase = PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId);

        if ($currentPhase === LebenszielPhaseId::PHASE_3) {
            $openLoans = MoneySheetState::getOpenLoansForPlayer($gameEvents, $playerId);
            if (count($openLoans) > 0) {
                return new AktionValidationResult(
                    canExecute: false,
                    reason: 'Du kannst das Spiel nicht beenden, solange du noch offene Kredite hast',
                );
            }
        }

        return parent::validate($gameEvents, $playerId);
    }
}
