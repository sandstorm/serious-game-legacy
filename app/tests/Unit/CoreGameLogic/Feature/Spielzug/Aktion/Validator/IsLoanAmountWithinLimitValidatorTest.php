<?php

declare(strict_types=1);

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsLoanAmountWithinLimitValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Tests\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->setupBasicGame();
});

describe('LoanCalculator::getMaxLoanAmountForPlayer', function () {
    it('is 80% of the Guthaben for a player without job, assets and loans', function () {
        /** @var TestCase $this */
        expect(LoanCalculator::getMaxLoanAmountForPlayer($this->getGameEvents(), $this->players[0]))
            ->toEqual(new MoneyAmount(Configuration::STARTKAPITAL_VALUE * 0.8));
    });

    it('subtracts the open repayments of existing loans', function () {
        /** @var TestCase $this */
        // total repayment for a loan of 10000 is 12000 -> (30000 + 10000) * 0.8 - 12000 = 20000
        $this->handle(TakeOutALoanForPlayer::create($this->players[0], 10000));

        expect(LoanCalculator::getMaxLoanAmountForPlayer($this->getGameEvents(), $this->players[0]))
            ->toEqual(new MoneyAmount(20000));
    });
});

describe('IsLoanAmountWithinLimitValidator', function () {
    it('fails if the loan amount is not positive', function (int $loanAmount) {
        /** @var TestCase $this */
        $result = new IsLoanAmountWithinLimitValidator($loanAmount)->validate($this->getGameEvents(), $this->players[0]);
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Der Kreditbetrag muss größer als 0 sein.');
    })->with([0, -1]);

    it('succeeds if the loan amount is exactly the credit limit', function () {
        /** @var TestCase $this */
        $result = new IsLoanAmountWithinLimitValidator((int) (Configuration::STARTKAPITAL_VALUE * 0.8))
            ->validate($this->getGameEvents(), $this->players[0]);
        expect($result->canExecute)->toBeTrue();
    });

    it('fails if the loan amount exceeds the credit limit', function () {
        /** @var TestCase $this */
        $result = new IsLoanAmountWithinLimitValidator((int) (Configuration::STARTKAPITAL_VALUE * 0.8) + 1)
            ->validate($this->getGameEvents(), $this->players[0]);
        expect($result->canExecute)->toBeFalse()
            ->and($result->reason)->toBe('Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.');
    });
});
