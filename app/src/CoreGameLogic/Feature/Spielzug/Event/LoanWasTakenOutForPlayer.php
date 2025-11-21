<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

class LoanWasTakenOutForPlayer implements GameEventInterface, ProvidesResourceChanges, Loggable
{
    public function __construct(
        public PlayerId $playerId,
        public Year     $year,
        public LoanId   $loanId,
        public LoanData $loanData
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            year: new Year($values['year']),
            loanId: new LoanId($values['loanId']),
            loanData: LoanData::fromArray($values['loanData']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'year' => $this->year,
            'loanId' => $this->loanId,
            'loanData' => $this->loanData->jsonSerialize(),
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return new ResourceChanges(
                guthabenChange: $this->loanData->loanAmount
            );
        }
        return new ResourceChanges();
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            text: "nimmt einen Kredit auf",
            playerId: $this->playerId,
            resourceChanges: $this->getResourceChanges($this->playerId),
        );
    }
}
