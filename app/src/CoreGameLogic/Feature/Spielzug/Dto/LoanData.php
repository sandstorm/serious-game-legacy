<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class LoanData
{
    public function __construct(
        public MoneyAmount     $loanAmount,
        public MoneyAmount     $totalRepayment,
        public MoneyAmount     $repaymentPerKonjunkturphase,
    ) {
    }


    /**
     * @param array{loanAmount: int, totalRepayment: float, repaymentPerKonjunkturphase: float} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            loanAmount: new MoneyAmount($values['loanAmount']),
            totalRepayment: new MoneyAmount($values['totalRepayment']),
            repaymentPerKonjunkturphase: new MoneyAmount($values['repaymentPerKonjunkturphase']),
        );
    }

    /**
     * @return array{loanAmount: float, totalRepayment: float, repaymentPerKonjunkturphase: float}
     */
    public function jsonSerialize(): array
    {
        return [
            'loanAmount' => $this->loanAmount->value,
            'totalRepayment' => $this->totalRepayment->value,
            'repaymentPerKonjunkturphase' => $this->repaymentPerKonjunkturphase->value,
        ];
    }
}
