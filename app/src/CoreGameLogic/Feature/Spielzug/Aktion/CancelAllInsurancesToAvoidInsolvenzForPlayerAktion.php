<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerANegativeBalanceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerAnyInsuranceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\InsuranceFinder;

class CancelAllInsurancesToAvoidInsolvenzForPlayerAktion extends Aktion
{
    public function __construct()
    {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new HasPlayerANegativeBalanceValidator();
        $validatorChain
            ->setNext(new HasPlayerAnyInsuranceValidator());
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot cancel insurance: ' . $result->reason, 1756987783);
        }

        $insurances = InsuranceFinder::getInstance()->getAllInsurances();
        $currentPlayerPhase = PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value;

        $totalCost = new MoneyAmount(0);
        $returnEvents = GameEventsToPersist::empty();

        foreach ($insurances as $insurance) {
            if (!MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $playerId, $insurance->id)) {
                // Player does not have this insurance, skip it
                continue;
            }
            $totalCost = $totalCost->add($insurance->getAnnualCost($currentPlayerPhase));
            $returnEvents = $returnEvents->withAppendedEvents(
                new InsuranceForPlayerWasCancelled(
                    playerId: $playerId,
                    insuranceId: $insurance->id,
                    resourceChanges: new ResourceChanges(
                        guthabenChange: $insurance->getAnnualCost($currentPlayerPhase)
                    ),
                )
            );

        }

        return $returnEvents;
    }
}
