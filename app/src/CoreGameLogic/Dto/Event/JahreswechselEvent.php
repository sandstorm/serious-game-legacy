<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

readonly final class JahreswechselEvent implements GameEventInterface
{

    public function __construct(
        public CurrentYear $year,
        public Leitzins $leitzins,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            year: new CurrentYear($values['year']),
            leitzins: new Leitzins($values['leitzins']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'year' => $this->year,
            'leitzins' => $this->leitzins,
        ];
    }
}
