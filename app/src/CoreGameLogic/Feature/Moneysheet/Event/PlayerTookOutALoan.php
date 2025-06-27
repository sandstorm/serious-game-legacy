<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Leitzins;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanAmount;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class PlayerTookOutALoan implements GameEventInterface
{
    // TODO is the period fixed?
    public const REPAYMENT_PERIOD = 20;

    public function __construct(
        public PlayerId $playerId,
        public string $intendedUse,
        public LoanAmount $loanAmount,
        public Leitzins $interestRate
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            intendedUse: $values['intendedUse'],
            loanAmount: new LoanAmount($values['loanAmount']),
            interestRate: new Leitzins($values['interestRate'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'intendedUse' => $this->intendedUse,
            'loanAmount' => $this->loanAmount,
            'interestRate' => $this->interestRate,
        ];
    }

    // TODO write this to the event?
    public function getRepaymentAmount(): MoneyAmount
    {
        return new MoneyAmount($this->loanAmount->value * (1 + $this->interestRate->value / self::REPAYMENT_PERIOD));
    }

    public function getRepaymentAmountPerRound(): MoneyAmount
    {
        return new MoneyAmount($this->getRepaymentAmount()->value / self::REPAYMENT_PERIOD);
    }
}
