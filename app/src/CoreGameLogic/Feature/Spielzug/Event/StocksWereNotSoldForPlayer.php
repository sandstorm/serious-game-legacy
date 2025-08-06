<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;

class StocksWereNotSoldForPlayer implements GameEventInterface, CommandInterface
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
}
