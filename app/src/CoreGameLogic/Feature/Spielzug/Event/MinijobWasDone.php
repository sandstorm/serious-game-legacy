<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
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

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->playerId)) {
            return new ResourceChanges(zeitsteineChange: -1);
        }
        return new ResourceChanges();
    }
}
