<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;

readonly abstract class Modifier
{
    public function __construct(
        public ModifierId $id,
        public PlayerTurn $startingTurn,
        public string     $description,
    ) {}

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    /**
     * By default modifiers will stay active until the end of the current Konjunkturphase.
     * @param GameEvents $gameEvents
     * @return bool
     */
    public function isActive(GameEvents $gameEvents): bool
    {
        $eventsAfterModifierBecameActive = $gameEvents->findAllAfterLastOrNullWhere(
            fn($event) => $event instanceof SpielzugWasEnded
                && $event->playerTurn->value === $this->startingTurn->value
        );
        if ($eventsAfterModifierBecameActive === null) {
            return true;
        }
        $konjunkturphaseWasChangedEvent = $eventsAfterModifierBecameActive->findLastOrNull(KonjunkturphaseWasChanged::class);
        return $konjunkturphaseWasChangedEvent === null;
    }

    public abstract function canModify(HookEnum $hook): bool;

    /**
     * @template T
     * @param T $value
     * @return T
     */
    public abstract function modify(mixed $value): mixed;
}
