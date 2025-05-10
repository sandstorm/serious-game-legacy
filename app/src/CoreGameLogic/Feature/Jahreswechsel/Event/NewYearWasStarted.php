<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Jahreswechsel\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class NewYearWasStarted implements GameEventInterface
{
    public function __construct(
        public CurrentYear $newYear,
        public Leitzins    $leitzins,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            newYear: new CurrentYear($values['newYear']),
            leitzins: new Leitzins($values['leitzins']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'newYear' => $this->newYear,
            'leitzins' => $this->leitzins,
        ];
    }
}
