<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

final readonly class InsuranceForPlayerWasCancelled implements GameEventInterface, Loggable
{
    public function __construct(
        public PlayerId     $playerId,
        public InsuranceId $insuranceId,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            insuranceId: InsuranceId::create($values['insuranceId']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'insuranceId' => $this->insuranceId,
        ];
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            playerId: $this->playerId,
            text: "kündigt '" . InsuranceFinder::getInstance()->findInsuranceById($this->insuranceId)->description . "'",
        );
    }
}
