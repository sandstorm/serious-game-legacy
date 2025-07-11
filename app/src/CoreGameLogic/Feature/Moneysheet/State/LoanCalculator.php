<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\Definitions\Configuration\Configuration;

class LoanCalculator
{
    public static function getMaxLoanAmount(float $guthaben, bool $hasJob): float
    {
        if ($guthaben <= 0) {
            return 0; // No loan can be taken if the balance is zero or negative
        }
        // without job only a loan of 80% of the current balance is allowed
        // if player has a job, they can take a loan of 10 times their current balance
        return $hasJob ? $guthaben * 10 : $guthaben * 0.8;
    }

    public static function getCalculatedTotalRepayment(float $loanAmount, float $zinssatz): float
    {
        $repaymentPeriod = Configuration::REPAYMENT_PERIOD;
        return $loanAmount * (1 + $zinssatz / $repaymentPeriod);
    }

    public static function getCalculatedRepaymentPerKonjunkturphase(float $loanAmount, float $zinssatz): float
    {
        return self::getCalculatedTotalRepayment($loanAmount, $zinssatz) / Configuration::REPAYMENT_PERIOD;
    }
}
