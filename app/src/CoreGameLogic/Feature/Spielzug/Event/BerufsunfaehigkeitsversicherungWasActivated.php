<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class BerufsunfaehigkeitsversicherungWasActivated implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public Year $year,
        public MoneyAmount $gehalt,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            year: new Year($values['year']),
            gehalt: new MoneyAmount($values['gehalt']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'gehalt' => $this->gehalt,
            'year' => $this->year,
        ];
    }
}
