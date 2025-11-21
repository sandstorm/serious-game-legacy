<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\PlayerId;

class LoanWasRepaidForPlayerInCaseOfInsolvenz implements GameEventInterface, Loggable
{
    public function __construct(
        public PlayerId $playerId,
        public LoanId $loanId,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            loanId: new LoanId($values['loanId']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'loanId' => $this->loanId,
        ];
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            text: "bekommt Kredit von Insolvenzverwaltung getilgt",
            playerId: $this->playerId,
        );
    }
}
