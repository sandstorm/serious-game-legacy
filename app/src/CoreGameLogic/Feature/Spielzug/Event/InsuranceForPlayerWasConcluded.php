<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

final readonly class InsuranceForPlayerWasConcluded implements GameEventInterface
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

}
