<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;

readonly final class BindZeitsteinForJobModifier extends Modifier
{
    public function __construct(public PlayerId $playerId, public PlayerTurn $playerTurn, string $description)
    {
        parent::__construct(ModifierId::BIND_ZEITSTEIN_FOR_JOB, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function isActive(GameEvents $gameEvents): bool
    {
        // Get all job events for this player, after the modifier was added
        $jobEventsAfterModifierStartingTurn = $gameEvents
            // all events after modifier the job was accepted (we use the playerTurn to find the correct event)
            ->findAllAfterLastOrNullWhere(fn($event) => $event instanceof JobOfferWasAccepted
                && $event->playerId->equals($this->playerId)
                && $event->playerTurn === $this->playerTurn)
            // filter the remaining events for JobOfferWasAccepted and JobWasQuit events (which would override or remove
            // the modifier) for the current player
            ?->filter(fn($event) => ($event instanceof JobOfferWasAccepted || $event instanceof JobWasQuit)
                && $event->playerId->equals($this->playerId));
        // if at least one event is in the list of events after the initial Job offer was accepted, the modifier is not
        // relevant anymore -> return false
        return $jobEventsAfterModifierStartingTurn !== null && count($jobEventsAfterModifierStartingTurn) === 0;
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::ZEITSTEINE;
    }

    public function modify(mixed $value): int
    {
        assert(is_int($value));
        return max([$value - 1, 0]);
    }

}
