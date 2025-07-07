<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Year;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class LoanWasTakenOutForPlayer implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId    $playerId,
        public Year        $year,
        public LoanId      $loanId,
        public string      $intendedUse,
        public MoneyAmount $loanAmount,
        public MoneyAmount $totalRepayment,
        public MoneyAmount $repaymentPerKonjunkturphase,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            year: new Year($values['year']),
            loanId: new LoanId($values['loanId']),
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
            'year' => $this->year,
            'loanId' => $this->loanId,
            'intendedUse' => $this->intendedUse,
            'loanAmount' => $this->loanAmount,
            'totalRepayment' => $this->totalRepayment,
            'repaymentPerKonjunkturphase' => $this->repaymentPerKonjunkturphase,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return new ResourceChanges(
                guthabenChange: $this->loanAmount
            );
        }
        return new ResourceChanges();
    }
}
