<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

final readonly class InsuranceForPlayerWasConcluded implements GameEventInterface, Loggable, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId     $playerId,
        public InsuranceId $insuranceId,
        public ResourceChanges $resourceChanges,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            insuranceId: InsuranceId::create($values['insuranceId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'insuranceId' => $this->insuranceId,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            text: "schließt '" . InsuranceFinder::getInstance()->findInsuranceById($this->insuranceId)->description . "' ab",
            playerId: $this->playerId,
            resourceChanges: $this->resourceChanges,
        );
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if($playerId->equals($this->playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }
}
