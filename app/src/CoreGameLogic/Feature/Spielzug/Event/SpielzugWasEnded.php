<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class SpielzugWasEnded implements GameEventInterface
{
    public function __construct(
        public PlayerId $player,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
        ];
    }
}
