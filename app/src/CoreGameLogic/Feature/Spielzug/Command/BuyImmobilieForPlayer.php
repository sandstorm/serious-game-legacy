<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class BuyImmobilieForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        CardId $cardId,
    ): BuyImmobilieForPlayer {
        return new self(
            $playerId,
            $cardId
        );
    }

    private function __construct(
        public PlayerId $playerId,
        public CardId $cardId,
    ) {
    }

}
