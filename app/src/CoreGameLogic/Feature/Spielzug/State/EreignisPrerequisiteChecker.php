<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\CardId;
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

    public function hasPlayerPrerequisites(PlayerId $playerId, EreignisPrerequisitesId $ereignisPrerequisitesId, ?CardId $requiredCardId = null): bool
    {
        return match ($ereignisPrerequisitesId) {
            EreignisPrerequisitesId::HAS_JOB => $this->hasPlayerAJob($playerId),
            EreignisPrerequisitesId::HAS_CHILD => $this->hasPlayerAChild($playerId),
            EreignisPrerequisitesId::HAS_NO_CHILD => !($this->hasPlayerAChild($playerId)),
            EreignisPrerequisitesId::HAS_SPECIFIC_CARD => $this->hasPlayerPlayedThisCard($playerId, $requiredCardId),
            EreignisPrerequisitesId::HAS_LOAN => $this->hasPlayerPlayedALoan($playerId),
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

    private function hasPlayerPlayedThisCard(PlayerId $playerId, CardId|null $requiredCardId): bool
    {
        if ($requiredCardId === null) {
            throw new \RuntimeException("Required Card Id not specified", 1755769328);
        }
        return PlayerState::hasPlayerPlayedSpecificCard($this->gameEvents, $playerId, $requiredCardId);

    }

    private function hasPlayerPlayedALoan(PlayerId $playerId): bool
    {
        $currentLoansForPlayer = MoneySheetState::getLoansForPlayer($this->gameEvents, $playerId);
        return count($currentLoansForPlayer) > 0;
    }
}
