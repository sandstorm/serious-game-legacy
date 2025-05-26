<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class SkipCard implements CommandInterface
{
    public function __construct(
        public PlayerId $player,
        public CardId   $card,
        public PileId   $pile,
    ) {
    }
}
