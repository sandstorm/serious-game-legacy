<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;

/**
 * This modifier **must** only be used for testing.
 * This will make sure no events will be triggered.
 * This modifier will be set in the default KonjunkturphaseDefinition for testing.
 */
readonly final class ForTestingOnlyNeverTriggerEreignisModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
    ) {
        parent::__construct(ModifierId::FOR_TESTING_ONLY_NEVER_TRIGGER_EREIGNIS, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function isActive(GameEvents $gameEvents): bool
    {
        return true;
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::FOR_TESTING_ONLY_NEVER_TRIGGER_EREIGNIS;
    }

    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }

}
