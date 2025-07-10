<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasKonjunkturphaseEndedValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerFilledOutMoneySheetValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class CompleteMoneySheetForPlayerAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('complete-money-sheet', 'Money Sheet vervollständigen');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validationChain = new HasKonjunkturphaseEndedValidator();
        $validationChain->setNext(new HasPlayerFilledOutMoneySheetValidator());

        return $validationChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot complete money sheet: ' . $result->reason, 1751375431);
        }

        $annualExpenses = MoneySheetState::getAnnualExpensesForPlayer($gameEvents, $playerId);

        // TODO add job income, stocks

        return GameEventsToPersist::with(
            new PlayerHasCompletedMoneysheetForCurrentKonjunkturphase(
                $playerId,
                KonjunkturphaseState::getCurrentYear($gameEvents),
                new ResourceChanges(
                    guthabenChange: new MoneyAmount($annualExpenses->value * -1)
                )
            )
        );
    }
}
