<?php

namespace Domain\CoreGameLogic\GameState;
use Domain\CoreGameLogic\Dto\Event\EventStream;
use Domain\CoreGameLogic\Dto\Event\Player\ProvidesModifiers;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;

class ModifierCalculator
{
    private function __construct(private EventStream $stream)
    {

    }

    public static function forStream(\Domain\CoreGameLogic\Dto\Event\EventStream $stream): self
    {
        return new self($stream);
    }

    public function forPlayer(\Domain\CoreGameLogic\Dto\ValueObject\PlayerId $playerId): ModifierCollection
    {
        return $this->stream->findAllOfType(ProvidesModifiers::class)->reduce(function (ModifierCollection $state, ProvidesModifiers $event) use ($playerId) {
            return $state->withAdditional($event->getModifiers($playerId));
        }, new ModifierCollection([]));
    }
}
