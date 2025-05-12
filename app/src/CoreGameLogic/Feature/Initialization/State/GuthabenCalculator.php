<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;

class GuthabenCalculator
{
    private function __construct(private GameEvents $stream)
    {

    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    public function forPlayer(PlayerId $playerId): Guthaben
    {
        return $this->stream->findAllOfType(ProvidesModifiers::class)->reduce(function (Guthaben $guthaben, ProvidesModifiers $event) use ($playerId) {
            $guthabenChange = $event->getModifiers($playerId)
                ->filter(fn(Modifier $modifier) => $modifier instanceof GuthabenChange)
                ->reduce(fn(int $guthabenChange, GuthabenChange $modifier) => $guthabenChange+$modifier->guthabenChange, 0);
            return $guthaben->withChange($guthabenChange);

        }, new Guthaben(0));
    }
}
