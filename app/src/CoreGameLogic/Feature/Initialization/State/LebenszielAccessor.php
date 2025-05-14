<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\Definitions\Lebensziel\Model\LebenszielDefinition;

class LebenszielAccessor
{
    private function __construct(private GameEvents $stream)
    {

    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    /**
     * @deprecated  please remove me and use PreGameState::lebenszielForPlayer instead.
     */
    public function forPlayer(PlayerId $playerId): ?LebenszielDefinition
    {
        /** @var LebenszielDefinition $lebensziel */
        $lebensziel = $this->stream->findAllOfType(LebenszielChosen::class)->filter(function ($event) use ($playerId) {
            return $event->playerId->equals($playerId);
        })[0]->lebensziel ?? null;
        if ($lebensziel === null) {
            return null;
        }
        $kompetenzsteinChanges = $this->stream
            ->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());
        // TODO: place kompetenzsteine in the currently active phase in the future
        $newPhase0 = $lebensziel->phases[0]->accumulate($kompetenzsteinChanges);
        $lebensziel = $lebensziel->withUpdatedPhase(0, $newPhase0);
        return $lebensziel;
    }
}
