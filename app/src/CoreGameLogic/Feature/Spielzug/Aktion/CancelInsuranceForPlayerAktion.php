<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

class CancelInsuranceForPlayerAktion extends Aktion
{
    public function __construct(private readonly InsuranceId $insuranceId)
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $playerId, $this->insuranceId);

        if (!$hasInsurance) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Versicherung wurde bereits gekündigt.',
            );
        }

        return new AktionValidationResult(
            canExecute: true,
        );
        // TODO beim Aussetzen nicht erlaubt?
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot cancel insurance: ' . $result->reason, 1751554652);
        }

        return GameEventsToPersist::with(
            new InsuranceForPlayerWasCancelled(
                playerId: $playerId,
                insuranceId: $this->insuranceId,
            )
        );
    }
}
