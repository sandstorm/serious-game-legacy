<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class CardWasActivated implements GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public CardId $card,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: new PlayerId($values['player']),
            card: new CardId($values['card']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'card' => $this->card,
        ];
    }
}
