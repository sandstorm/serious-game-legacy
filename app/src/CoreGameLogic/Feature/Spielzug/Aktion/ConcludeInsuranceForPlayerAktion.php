<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\PlayerDoesNotYetHaveThisInsuranceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

/**
 * An insurance may mitigate the effects of some EreignisCards. The player has to pay for the insurance immediately and at the end of
 * each Konjunkturphase they will pay for the upcoming year.
 */
class ConcludeInsuranceForPlayerAktion extends Aktion
{

    private ResourceChanges $resourceChanges;

    public function __construct(private readonly InsuranceId $insuranceId)
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $this->resourceChanges = $this->getResourceChanges($gameEvents, $playerId);

        $validatorChain = new PlayerDoesNotYetHaveThisInsuranceValidator($this->insuranceId);
        $validatorChain->setNext(new HasPlayerEnoughResourcesValidator($this->resourceChanges));
        return $validatorChain->validate($gameEvents, $playerId);
    }

    private function getResourceChanges(GameEvents $gameEvents, PlayerId $playerId): ResourceChanges
    {
        $currentPhaseForPlayer = PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value;
        $insuranceCost = InsuranceFinder::getInstance()->findInsuranceById($this->insuranceId)->getAnnualCost($currentPhaseForPlayer)->value;
        return new ResourceChanges(guthabenChange: new MoneyAmount(-$insuranceCost));

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
                resourceChanges: $this->resourceChanges,
            )
        );
    }
}
