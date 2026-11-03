<?php

declare(strict_types=1);

namespace Tests\Unit\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;

describe('getMaxLoanAmount', function () {
    it('calculates the max loan amount without a job', function () {
        $assets = 1000;
        $salary = 0;
        $obligations = 0;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(800.0);
    });

    it('calculates the max loan amount without a job and with obligations', function () {
        $assets = 1000;
        $salary = 0;
        $obligations = 500;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(300.0);
    });

    it('calculates the max loan amount without a job, with obligations and an Insolvenz', function () {
        $assets = 1000;
        $salary = 0;
        $obligations = 500;
        $wasPlayerInsolventInThePast = true;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(0);
    });

    it('calculates the max loan amount without a job and with huge obligations', function () {
        $assets = 1000;
        $salary = 0;
        $obligations = 1500;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(-700.0);
    });

    it('calculates the max loan amount with a job', function () {
        $assets = 1000;
        $salary = 100;
        $obligations = 0;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(1500.0);
    });

    it('calculates the max loan amount with a job and obligations', function () {
        $assets = 1000;
        $salary = 100;
        $obligations = 500;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(1000.0);
    });

    it('calculates the max loan amount with a job, obligations and an Insolvenz', function () {
        $assets = 1000;
        $salary = 100;
        $obligations = 500;
        $wasPlayerInsolventInThePast = true;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        expect($maxLoanAmount)->toEqual(700.0);
    });
});

describe('repayment', function () {
    it('calculates the total repayment', function () {
        $loanAmount = 1000.0;
        $zinssatz = 5.0; // 5%
        $totalRepayment = LoanCalculator::getCalculatedTotalRepayment($loanAmount, $zinssatz);
        expect($totalRepayment)->toEqual(1250.0);
    });

    it('calculates the repayment per Konjunkturphase', function () {
        $loanAmount = 1000.0;
        $zinssatz = 5.0; // 5%
        $repaymentPerPhase = LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($loanAmount, $zinssatz);
        expect($repaymentPerPhase)->toEqual(62.5);
    });

    it('calculates the costs for a loan repayment', function () {
        $openRepayment = 1000.0;
        $costs = LoanCalculator::getCostsForLoanRepayment($openRepayment)->value;
        expect($costs)->toEqual(1010.0);
    });
});
