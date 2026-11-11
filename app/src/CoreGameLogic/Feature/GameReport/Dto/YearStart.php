<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * The start of a Konjunkturphase, $turnIndex is the number of Spielzüge played before it started.
 */
final readonly class YearStart
{
    public function __construct(
        public int $turnIndex,
        public Year $year,
        public KonjunkturphaseTypeEnum $type,
        public string $name,
    ) {
    }
}
