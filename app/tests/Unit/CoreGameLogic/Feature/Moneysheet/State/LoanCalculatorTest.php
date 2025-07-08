<?php

declare(strict_types=1);

namespace Tests\Unit\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use PHPUnit\Framework\TestCase;

class LoanCalculatorTest extends TestCase
{
    public function testGetMaxLoanAmountWithoutJob(): void
    {
        $guthaben = 1000.0;
        $hasJob = false;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($guthaben, $hasJob);
        $this->assertEquals(800.0, $maxLoanAmount);
    }

    public function testGetMaxLoanAmountWithJob(): void
    {
        $guthaben = 1000.0;
        $hasJob = true;
        $maxLoanAmount = LoanCalculator::getMaxLoanAmount($guthaben, $hasJob);
        $this->assertEquals(10000.0, $maxLoanAmount);
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
}
