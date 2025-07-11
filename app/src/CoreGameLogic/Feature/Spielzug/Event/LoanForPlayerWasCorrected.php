<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\UpdatesInputForLoan;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;

final readonly class LoanForPlayerWasCorrected implements GameEventInterface, ProvidesResourceChanges, UpdatesInputForLoan
{
    public function __construct(
        public PlayerId     $playerId,
        public LoanId $loanId,
        private LoanData $correctLoan,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            loanId: new LoanId($values['loanId']),
            correctLoan: LoanData::fromArray($values['correctLoan']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'loanId' => $this->loanId,
            'correctLoan' => $this->correctLoan->jsonSerialize(),
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId === $this->playerId) {
            return new ResourceChanges(guthabenChange: new MoneyAmount(Configuration::FINE_VALUE * -1));
        }
        return new ResourceChanges();
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
        return $this->correctLoan;
    }
}
