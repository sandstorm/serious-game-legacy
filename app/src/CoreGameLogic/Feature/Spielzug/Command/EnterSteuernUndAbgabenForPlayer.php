<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class EnterSteuernUndAbgabenForPlayer implements CommandInterface
{
    public static function create(PlayerId $playerId, MoneyAmount $input): EnterSteuernUndAbgabenForPlayer
    {
        return new self($playerId, $input);
    }

    private function __construct(
        public PlayerId $playerId,
        public MoneyAmount $input,
    ) {
    }
}
