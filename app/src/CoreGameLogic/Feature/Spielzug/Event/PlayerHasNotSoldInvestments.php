<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\PlayerId;

class PlayerHasNotSoldInvestments implements GameEventInterface, CommandInterface, Loggable
{
    /**
     * @param PlayerId $playerId
     */
    public function __construct(
        public PlayerId    $playerId,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
        ];
    }
    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            playerId: $this->playerId,
            text: "verkauft keine Investitionen",
        );
    }
}
