<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;
use Domain\CoreGameLogic\Dto\Event\Player\ProvidesModifiers;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

class ModifierCalculator
{
    private function __construct(private GameEvents $stream)
    {

    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    public function forPlayer(PlayerId $playerId): ModifierCollection
    {
        return $this->stream->findAllOfType(ProvidesModifiers::class)->reduce(function (ModifierCollection $state, ProvidesModifiers $event) use ($playerId) {
            return $state->withAdditional($event->getModifiers($playerId));
        }, new ModifierCollection([]));
    }
}
