<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;

/**
 * This modifier is used when the player draws an EreignisCard that will cause them to lose their job but allows
 * a Berufsunfähigkeitsversicherung to mitigate the consequences. In addition to losing their job, the player
 * will also not be allowed to take a new job until the end of the current Konjunkturphase.
 */
readonly final class BerufsunfaehigkeitJobsperreModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
    ) {
        parent::__construct(ModifierId::BERUFSUNFAEHIGKEIT_JOBSPERRE, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::BERUFSUNFAEHIGKEIT_JOBSPERRE;
    }

    /**
     * @param mixed $value has player a jobsperre (is forbidden to take a job this Konjunkturphase)
     * @return bool
     */
    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }

}
