<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

readonly final class InitLebenszielEvent implements GameEventInterface
{

    public function __construct(
        public Lebensziel $lebensziel,
        public PlayerId $player
        // TODO phases, goals, etc
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            lebensziel: new Lebensziel($values['lebensziel']),
            player: new PlayerId($values['player']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'lebensziel' => $this->lebensziel,
            'player' => $this->player,
        ];
    }
}
