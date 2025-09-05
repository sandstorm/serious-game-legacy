<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\Definitions\Configuration\Configuration;

class LoanCalculator
{
    public static function getMaxLoanAmount(float $guthaben, bool $hasJob, bool $wasInsolvent): float
    {
        if ($guthaben <= 0) {
            return 0; // No loan can be taken if the balance is zero or negative
        }
        if ($hasJob) {
            return $guthaben * 10; // if player has a job, they can take a loan of 10 times their current balance
        } else if ($wasInsolvent) {
            return $guthaben * 0.5; // without job and if the player was insolvent once already this game only 50% of current balance is allowed
        } else {
            return $guthaben * 0.8; // without job only a loan of 80% of the current balance is allowed
        }
    }

    public static function getCalculatedTotalRepayment(float $loanAmount, float $zinssatz): float
    {
        $repaymentPeriod = Configuration::REPAYMENT_PERIOD;
        return round($loanAmount * (1 + $zinssatz / $repaymentPeriod), 2);
    }

    public static function getCalculatedRepaymentPerKonjunkturphase(float $loanAmount, float $zinssatz): float
    {
        return round(self::getCalculatedTotalRepayment($loanAmount, $zinssatz) / Configuration::REPAYMENT_PERIOD, 2);
    }

    public static function equals(float $value1, float $value2): bool
    {
        return abs($value1 - $value2) < 0.01;
    }
}
