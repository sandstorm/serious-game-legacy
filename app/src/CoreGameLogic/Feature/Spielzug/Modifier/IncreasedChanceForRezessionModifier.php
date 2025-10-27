<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\KonjunkturphaseCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * A Konjunkturphase may have an increased chance, that a Rezession will follow. If that is the case, the next
 * Konjunkturphase will have a fixed chance to be a Rezession.
 *
 * @see KonjunkturphaseCommandHandler::handleChangeKonjunkturphase()
 * @see KonjunkturphaseFinder::getRandomKonjunkturphase()
 * @see KonjunkturphaseFinder::getListOfPossibleNextPhaseTypes()
 */
readonly final class IncreasedChanceForRezessionModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
        protected Year $year,
    ) {
        parent::__construct(ModifierId::INCREASED_CHANCE_FOR_REZESSION, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function isActive(GameEvents $gameEvents): bool
    {
        $konjunkturphaseChangesAfterSperre = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof KonjunkturphaseWasChanged && $event->year->equals(new Year($this->year->value + 1))
        );

        return $konjunkturphaseChangesAfterSperre === null;
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::INCREASED_CHANCE_FOR_REZESSION;
    }

    /**
     * @param mixed $value is there an increased chance for a rezession?
     * @return bool
     */
    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }
}
