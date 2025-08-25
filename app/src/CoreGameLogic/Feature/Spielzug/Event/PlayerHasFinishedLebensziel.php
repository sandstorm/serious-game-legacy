<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class PlayerHasFinishedLebensziel implements GameEventInterface, ProvidesResourceChanges, Loggable
{
    public function __construct(
        public PlayerId        $playerId,
        public ResourceChanges $resourceChanges,
        public string          $lebenszielName,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
            lebenszielName: $values['lebenszielName'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'resourceChanges' => $this->resourceChanges,
            'lebenszielName' => $this->lebenszielName,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if($playerId->equals($this->playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            playerId: $this->playerId,
            text: "erreicht Lebensziel '" . $this->lebenszielName . "'",
            resourceChanges: $this->resourceChanges,
        );
    }
}
