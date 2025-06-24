<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;

final readonly class KonjunkturphaseHasEnded implements GameEventInterface
{
    public function __construct(
        public CurrentYear $year
    ) {
    }

    /**
     * @param array<string, mixed> $values
     * @return GameEventInterface
     */
    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            year: new CurrentYear($values['year']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            "year" => $this->year,
        ];
    }
}
