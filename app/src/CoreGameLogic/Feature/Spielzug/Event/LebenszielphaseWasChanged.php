<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class LebenszielphaseWasChanged implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId          $playerId,
        public ResourceChanges   $resourceChanges,
        public int               $currentPhase,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
            currentPhase: $values['currentPhase'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'resourceChanges' => $this->resourceChanges,
            'currentPhase' => $this->currentPhase,
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
}
