<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\UpdatesInputForLoan;
use Domain\CoreGameLogic\PlayerId;

final readonly class LoanForPlayerWasEntered implements GameEventInterface, UpdatesInputForLoan
{
    public function __construct(
        public PlayerId  $playerId,
        public LoanId    $loanId,
        private LoanData $loanInput,
        private LoanData $expectedLoan,
        private bool     $wasInputCorrect
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            loanId: new LoanId($values['loanId']),
            loanInput: LoanData::fromArray($values['loanInput']),
            expectedLoan: LoanData::fromArray($values['expectedLoan']),
            wasInputCorrect: $values['wasInputCorrect'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'loanId' => $this->loanId,
            'loanInput' => $this->loanInput->jsonSerialize(),
            'expectedLoan' => $this->expectedLoan->jsonSerialize(),
            'wasInputCorrect' => $this->wasInputCorrect,
        ];
    }

    public function wasInputCorrect(): bool
    {
        return $this->wasInputCorrect;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getLoanId(): LoanId
    {
        return $this->loanId;
    }

    public function getLoanData(): LoanData
    {
        return $this->loanInput;
    }

    public function getExpectedLoanData(): LoanData
    {
        return $this->expectedLoan;
    }
}
