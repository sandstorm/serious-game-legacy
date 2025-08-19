<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class FileInsolvenzForPlayer implements CommandInterface
{
    public static function create(PlayerId $playerId): CommandInterface
    {
        return new self($playerId);
    }

    private function __construct(protected PlayerId $player)
    {
    }

    public function getPlayer(): PlayerId
    {
        return $this->player;
    }
}
