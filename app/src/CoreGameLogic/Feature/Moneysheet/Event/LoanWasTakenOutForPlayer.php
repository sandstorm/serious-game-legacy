<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class LoanWasTakenOutForPlayer implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public string $intendedUse,
        public MoneyAmount $loanAmount,
        public MoneyAmount $totalRepayment,
        public MoneyAmount $repaymentPerKonjunkturphase,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            intendedUse: $values['intendedUse'],
            loanAmount: new MoneyAmount($values['loanAmount']),
            totalRepayment: new MoneyAmount($values['totalRepayment']),
            repaymentPerKonjunkturphase: new MoneyAmount($values['repaymentPerKonjunkturphase']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'intendedUse' => $this->intendedUse,
            'loanAmount' => $this->loanAmount,
            'totalRepayment' => $this->totalRepayment,
            'repaymentPerKonjunkturphase' => $this->repaymentPerKonjunkturphase,
        ];
    }
}
