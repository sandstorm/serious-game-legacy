<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class MiniJobWasStarted implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public CardID    $miniJobCardId,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            minijobcardId: CardId::fromString($values['miniJobCardID'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'miniJobCardID' => $this->miniJobCardId,
        ];
    }
}
