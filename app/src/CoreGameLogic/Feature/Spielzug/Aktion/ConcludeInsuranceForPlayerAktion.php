<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

class ConcludeInsuranceForPlayerAktion extends Aktion
{
    private InsuranceId $insuranceId;

    public function __construct(InsuranceId $insuranceId)
    {
        parent::__construct('conclude-insurance', 'Versicherung abschließen');
        $this->insuranceId = $insuranceId;
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $playerId, $this->insuranceId);

        if ($hasInsurance) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Versicherung wurde bereits abgeschlossen.',
            );
        }

        return new AktionValidationResult(
            canExecute: true,
        );
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot conclude insurance: ' . $result->reason, 1751554652);
        }

        return GameEventsToPersist::with(
            new InsuranceForPlayerWasConcluded(
                playerId: $playerId,
                insuranceId: $this->insuranceId,
            )
        );
    }
}
