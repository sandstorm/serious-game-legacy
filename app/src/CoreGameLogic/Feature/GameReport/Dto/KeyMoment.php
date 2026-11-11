<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * Something worth retelling in the game report. The text follows the player name, like in the Ereignisprotokoll.
 */
final readonly class KeyMoment
{
    public function __construct(
        public Year $year,
        public PlayerId $playerId,
        public KeyMomentType $type,
        public string $text,
        public ?string $detail = null,
        public ?MoneyAmount $guthabenChange = null,
    ) {
    }
}
