<?php

declare(strict_types=1);

namespace Tests\Unit\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use PHPUnit\Framework\TestCase;

class LoanCalculatorTest extends TestCase
{
    public function testGetMaxLoanAmountWithoutJob(): void
    {
        $assets = 1000;
        $salary = 0;
        $obligations = 0;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        $this->assertEquals(800.0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithoutJobAndObligations(): void
    {
        $assets = 1000;
        $salary = 0;
        $obligations = 500;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations,$wasPlayerInsolventInThePast)->value;
        $this->assertEquals(300.0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithoutJobAndObligationsAndInsolvenz(): void
    {
        $assets = 1000;
        $salary = 0;
        $obligations = 500;
        $wasPlayerInsolventInThePast = true;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations,$wasPlayerInsolventInThePast)->value;
        $this->assertEquals(0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithoutJobAndHugeObligations(): void
    {
        $assets = 1000;
        $salary = 0;
        $obligations = 1500;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations, $wasPlayerInsolventInThePast)->value;
        $this->assertEquals(-700.0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithJob(): void
    {
        $assets = 1000;
        $salary = 100;
        $obligations = 0;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations,$wasPlayerInsolventInThePast)->value;
        $this->assertEquals(1500.0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithJobAndObligations(): void
    {
        $assets = 1000;
        $salary = 100;
        $obligations = 500;
        $wasPlayerInsolventInThePast = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations,$wasPlayerInsolventInThePast)->value;
        $this->assertEquals(1000.0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithJobAndObligationsAndInsolvenz(): void
    {
        $assets = 1000;
        $salary = 100;
        $obligations = 500;
        $wasPlayerInsolventInThePast = true;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($assets, $salary, $obligations,$wasPlayerInsolventInThePast)->value;
        $this->assertEquals(700.0, $maxLoanAmount);
    }

    public function testGetCalculatedTotalRepayment(): void
    {
        $loanAmount = 1000.0;
        $zinssatz = 5.0; // 5%
        $totalRepayment = LoanCalculator::getCalculatedTotalRepayment($loanAmount, $zinssatz);
        $this->assertEquals(1250.0, $totalRepayment);
    }

    public function testGetCalculatedRepaymentPerKonjunkturphase(): void
    {
        $loanAmount = 1000.0;
        $zinssatz = 5.0; // 5%
        $repaymentPerPhase = LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($loanAmount, $zinssatz);
        $this->assertEquals(62.5, $repaymentPerPhase);
    }

    public function testGetCostsForLoanRepayment(): void
    {
        $openRepayment = 1000.0;
        $costs = LoanCalculator::getCostsForLoanRepayment($openRepayment)->value;
        $this->assertEquals(1010.0, $costs);
    }
}
