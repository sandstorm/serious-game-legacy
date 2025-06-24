<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\PlayerId;

final readonly class PlayerHasCompletedMoneysheetForCurrentKonjunkturphase implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public CurrentYear $year,
    ) {
    }

    /**
     * @param array<string, mixed> $values
     * @return GameEventInterface
     */
    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
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
            "playerId" => $this->playerId,
        ];
    }
}
