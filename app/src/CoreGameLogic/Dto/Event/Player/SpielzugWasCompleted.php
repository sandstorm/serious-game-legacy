<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

readonly final class SpielzugWasCompleted implements GameEventInterface
{

    public function __construct(
        public PlayerId $player,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: new PlayerId($values['player']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
        ];
    }
}
