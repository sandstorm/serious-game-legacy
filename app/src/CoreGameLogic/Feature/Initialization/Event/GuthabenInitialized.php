<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChangeCollection;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

final readonly class GuthabenInitialized implements ProvidesResourceChanges, GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public ResourceChanges $guthabenChange,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            guthabenChange: new ResourceChanges(guthabenChange: $values['guthabenChange']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'guthabenChange' => $this->guthabenChange->guthabenChange,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChangeCollection
    {
        if ($this->playerId->equals($playerId)) {
            return new ResourceChangeCollection([new ResourceChanges(guthabenChange: $this->guthabenChange->guthabenChange)]);
        }
        return new ResourceChangeCollection([]);
    }
}
