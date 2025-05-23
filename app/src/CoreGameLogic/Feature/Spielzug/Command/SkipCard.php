<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

final readonly class SkipCard implements CommandInterface
{
    public function __construct(
        public PlayerId $player,
        public CardId $card,
        public PileId $pile,
    ) {
    }
}
