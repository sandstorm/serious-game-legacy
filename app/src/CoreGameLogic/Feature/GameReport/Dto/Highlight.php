<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\CoreGameLogic\PlayerId;

/**
 * A notable fact of the game. $playerId is null when the highlight is about all players.
 */
final readonly class Highlight
{
    public function __construct(
        public HighlightType $type,
        public ?PlayerId $playerId,
        public string $title,
        public string $text,
    ) {
    }
}
