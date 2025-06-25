<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;

final readonly class MinijobWasDone implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public CardId   $minijobCardId,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            minijobcardId: CardId::fromString($values['minijobCardId'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'minijobCardId' => $this->minijobCardId,
        ];
    }
}
