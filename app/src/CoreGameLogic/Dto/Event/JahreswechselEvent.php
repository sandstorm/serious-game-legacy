<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;

readonly final class JahreswechselEvent
{

    public function __construct(
        public CurrentYear $year,
        public Leitzins $leitzins,
    )
    {
    }
}
