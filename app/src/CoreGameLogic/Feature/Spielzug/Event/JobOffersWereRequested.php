<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class JobOffersWereRequested implements GameEventInterface, ProvidesResourceChanges
{
    /**
     * @param JobCardDefinition[] $jobs
     */
    public function __construct(
        public PlayerId $player,
        public array $jobs,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            jobs: array_map(fn ($job) => JobCardDefinition::fromString($values['jobs']), $values['jobs']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'jobs' => $this->jobs,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($playerId->equals($this->player)) {
            // Skipping will always consume 1 Zeitstein
            return new ResourceChanges(zeitsteineChange: -1);
        }
        return new ResourceChanges();
    }
}
