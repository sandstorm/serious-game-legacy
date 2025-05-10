<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class LebenszielChosen implements GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public Lebensziel $lebensziel,
        // TODO phases, goals, etc
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: new PlayerId($values['player']),
            lebensziel: new Lebensziel($values['lebensziel']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'lebensziel' => $this->lebensziel,
        ];
    }
}
