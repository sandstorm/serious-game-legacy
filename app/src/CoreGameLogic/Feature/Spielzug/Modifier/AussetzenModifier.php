<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;

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

    public function isActive(GameEvents $gameEvents): bool
    {
        return PlayerState::getCurrentTurnForPlayer($gameEvents,
                $this->playerId)->value <= $this->playerTurn->value + $this->numberOfSkippedTurns;
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
