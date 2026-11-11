<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class PlayerReport
{
    /**
     * @param array<int, int> $phaseReachedInYear the year in which each Lebenszielphase was reached, keyed by phase
     * @param CareerStep[] $career
     * @param InvestmentResult[] $investmentResults
     * @param CardExpense[] $topCardExpenses the most expensive Kategorie cards, most expensive first
     */
    public function __construct(
        public PlayerId $playerId,
        public string $name,
        public string $colorClass,
        public ?string $lebenszielName,
        public LebenszielPhaseId $reachedPhase,
        public array $phaseReachedInYear,
        public bool $hasFinishedLebensziel,
        public MoneyAmount $finalGuthaben,
        public ?MoneyAmount $assetsValue,
        public MoneyAmount $openLoanRepayments,
        public MoneyBreakdown $breakdown,
        public array $career,
        public array $investmentResults,
        public array $topCardExpenses,
        public int $moneysheetInputs,
        public int $correctMoneysheetInputs,
        public int $numberOfMinijobs,
    ) {
    }

    /**
     * Guthaben plus the current value of investments and Immobilien minus what is still to be paid for loans.
     * Null if the value of the assets is unknown, e.g. for an Immobilie that no longer exists in the definitions.
     */
    public function getNetWorth(): ?MoneyAmount
    {
        return $this->assetsValue?->add($this->finalGuthaben)->subtract($this->openLoanRepayments);
    }

    public function getNumberOfJobs(): int
    {
        return count(array_filter($this->career, fn (CareerStep $step) => !$step->isQuit()));
    }
}
