<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasPutBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\CardWithResourceChanges;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class AktionsCalculator
{
    private function __construct(
        private GameEvents $stream,
    ) {
    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    // TODO maybe return an object with failed requirements?
    public function canPlayerAffordAction(PlayerId $playerId, ResourceChanges $cost): bool
    {
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $playerId);
        if (
            ($cost->guthabenChange->equals(0) || $cost->guthabenChange->value * -1 <= $playerResources->guthabenChange->value) &&
            $cost->zeitsteineChange * -1 <= $playerResources->zeitsteineChange &&
            $cost->bildungKompetenzsteinChange * -1 <= $playerResources->bildungKompetenzsteinChange &&
            $cost->freizeitKompetenzsteinChange * -1 <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }

    public function getEventsThisTurn(): GameEvents
    {
        return $this->stream->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $this->stream->findAllAfterLastOfType(GameWasStarted::class);
    }

    public function hasPlayerSkippedACardThisRound(PlayerId $playerId): bool
    {
        $eventsThisTurn = $this->getEventsThisTurn();
        $cardWasSkipped = $eventsThisTurn->findLastOrNullWhere( fn($event) => $event instanceof CardWasSkipped && $event->getPlayerId()->equals($playerId));
        return $cardWasSkipped !== null;
    }

    public function hasPlayerPlayedACardOrPutOneBack(PlayerId $playerId): bool
    {
        $eventsThisTurn = $this->getEventsThisTurn();
        $cardWasDiscarded = $eventsThisTurn->findLastOrNullWhere( fn($event) => $event instanceof CardWasPutBackOnTopOfPile && $event->getPlayerId()->equals($playerId));
        $cardWasPlayed = $eventsThisTurn->findLastOrNullWhere( fn($event) => $event instanceof CardWasActivated && $event->getPlayerId()->equals($playerId));

        if ($cardWasDiscarded !== null || $cardWasPlayed !== null) {
            return true;
        }

        return false;
    }

    function canPlayerAffordJobCard(PlayerId $player, JobCardDefinition $card): bool
    {
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $player);
        if (
            $card->getRequirements()->zeitsteine <= $playerResources->zeitsteineChange &&
            $card->getRequirements()->bildungKompetenzsteine <= $playerResources->bildungKompetenzsteinChange &&
            $card->getRequirements()->freizeitKompetenzsteine <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }

    /**
     * Returns the modified cost for a Card. Konjunkturphasen may change the cost of a card/category. Use this
     * function to get the actual costs and do **not** use `CardWithResourceChanges->getResourceChanges()` unless
     * you know what you are doing.
     * @param CardWithResourceChanges&CardDefinition $card
     * @return ResourceChanges
     */
    public function getModifiedResourceChangesForCard(CardDefinition & CardWithResourceChanges $card): ResourceChanges
    {
        return match ($card->getCategory()->name) {
            CategoryId::BILDUNG_UND_KARRIERE->name => ModifierCalculator::forStream($this->stream)
                ->withoutPlayer()
                ->modify($this->stream, HookEnum::BILDUNG_UND_KARRIERE_COST, $card->getResourceChanges()),
            CategoryId::SOZIALES_UND_FREIZEIT->name => ModifierCalculator::forStream($this->stream)
                ->withoutPlayer()
                ->modify($this->stream, HookEnum::SOZIALES_UND_FREIZEIT_COST, $card->getResourceChanges()),
            default => $card->getResourceChanges(),
        };
    }
}
