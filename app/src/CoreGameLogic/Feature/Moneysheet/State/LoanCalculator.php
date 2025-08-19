<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;

class LoanCalculator
{
    public static function getMaxLoanAmount(float $sumOfAlAssets, float $salary, float $obligations, bool $wasPlayerInsolventInThePast): MoneyAmount
    {
        if ($salary > 0) {
            // if player has job: limit is 5 times the salary + assets - obligations
            return $wasPlayerInsolventInThePast
                ? new MoneyAmount($salary * 2 + $sumOfAlAssets - $obligations)
                : new MoneyAmount($salary * 5 + $sumOfAlAssets - $obligations);
        } else {
            // if player has no job: limit is 80% of the assets - obligations
            return $wasPlayerInsolventInThePast
                ? new MoneyAmount($sumOfAlAssets * 0.5 - $obligations)
                : new MoneyAmount($sumOfAlAssets * 0.8 - $obligations);
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

    public static function getCostsForLoanRepayment(float $openRepayment): MoneyAmount
    {
        return new MoneyAmount($openRepayment * 1.01);
    }
}
