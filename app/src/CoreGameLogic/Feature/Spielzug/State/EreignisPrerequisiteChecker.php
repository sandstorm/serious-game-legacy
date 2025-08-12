<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;

final readonly class EreignisPrerequisiteChecker
{
    private function __construct(
        private GameEvents $gameEvents,
    ) {
    }

    public static function forStream(GameEvents $gameEvents): self
    {
        return new self($gameEvents);
    }

    public function hasPlayerPrerequisites(PlayerId $playerId, EreignisPrerequisitesId $ereignisPrerequisitesId): bool
    {
        return match ($ereignisPrerequisitesId) {
            EreignisPrerequisitesId::HAS_JOB => $this->hasPlayerAJob($playerId),
            EreignisPrerequisitesId::HAS_CHILD => $this->hasPlayerAChild($playerId),
            EreignisPrerequisitesId::HAS_NO_CHILD => !($this->hasPlayerAChild($playerId)),
            EreignisPrerequisitesId::NO_PREREQUISITES => true,
        };
    }

    private function hasPlayerAJob(PlayerId $playerId): bool
    {
        return PlayerState::getJobForPlayer($this->gameEvents, $playerId) !== null;
    }

    private function hasPlayerAChild(PlayerId $playerId): bool
    {
        return PlayerState::hasChild($this->gameEvents, $playerId);
    }
}
