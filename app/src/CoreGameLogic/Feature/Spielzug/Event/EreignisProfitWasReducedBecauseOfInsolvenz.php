<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

/**
 * In case a player is insolvent and they gain money through an Ereignis, they have to give away half of that money as Insolvenzabgaben.
 */
final readonly class EreignisProfitWasReducedBecauseOfInsolvenz implements GameEventInterface, ProvidesResourceChanges, Loggable
{
    public function __construct(
        protected PlayerId        $playerId,
        protected ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
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
            text: "zahlt Insolvenzabgaben",
            playerId: $this->playerId,
            resourceChanges: $this->resourceChanges,
        );
    }
}
