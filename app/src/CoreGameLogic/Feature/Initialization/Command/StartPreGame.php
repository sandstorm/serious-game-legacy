<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

readonly class StartPreGame implements CommandInterface
{
    public static function create(int $numberOfPlayers): self
    {
        return new self($numberOfPlayers);
    }

    /**
     * @param PlayerId[] $fixedPlayerIdsForTesting
     */
    private function __construct(public int $numberOfPlayers, public array $fixedPlayerIdsForTesting = [])
    {
        assert($this->numberOfPlayers > 0, 'Number of players must be greater than 0');
    }

    public function withFixedPlayerIdsForTesting(PlayerId ...$playerIds): self
    {
        assert(count($playerIds) === $this->numberOfPlayers, 'incorrect number of PlayerIds given');
        return new self($this->numberOfPlayers, $playerIds);
    }
}
