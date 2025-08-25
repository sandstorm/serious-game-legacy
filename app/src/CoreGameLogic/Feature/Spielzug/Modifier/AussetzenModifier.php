<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\DoesNotSkipTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;

/**
 * This modifier will disallow any Zeitstein-Aktionen and most actions that change the players resources.
 * Make sure to use the @see DoesNotSkipTurnValidator in any Aktion that should not be allowed when the player
 * skips a turn.
 */
readonly final class AussetzenModifier extends Modifier
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerTurn $playerTurn,
        string $description,
        public int $numberOfSkippedTurns,
    ) {
        parent::__construct(ModifierId::AUSSETZEN, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    /**
     * Remains active for the specified number of turns.
     * Attention: This might result in no player being able to do anything until all turns are skipped.
     * @param GameEvents $gameEvents
     * @return bool
     */
    public function isActive(GameEvents $gameEvents): bool
    {
        return PlayerState::getCurrentTurnForPlayer($gameEvents, $this->playerId)->value <= $this->playerTurn->value + $this->numberOfSkippedTurns;
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::AUSSETZEN;
    }

    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }

}
