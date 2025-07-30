<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class MaybeTriggerEreignis implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        CategoryId $categoryId,
    ): MaybeTriggerEreignis {
        return new self($playerId, $categoryId);
    }

    private function __construct(
        public PlayerId $playerId,
        public CategoryId $categoryId,
    ) {
    }

}
