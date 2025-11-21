<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\PlayerId;

final readonly class JobWasQuit implements GameEventInterface, Loggable
{
    public function __construct(
        public PlayerId $playerId,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
        );
    }
    public function jsonSerialize(): array
    {
        return[
        'playerId' => $this->playerId,
        ];
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            text: "ist jetzt arbeitslos",
            playerId: $this->playerId,
        );
    }
}
