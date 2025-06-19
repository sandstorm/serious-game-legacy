<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\CardDefinition;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;

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
            $cost->guthabenChange->value * -1 <= $playerResources->guthabenChange->value &&
            $cost->zeitsteineChange * -1 <= $playerResources->zeitsteineChange &&
            $cost->bildungKompetenzsteinChange * -1 <= $playerResources->bildungKompetenzsteinChange &&
            $cost->freizeitKompetenzsteinChange * -1 <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }

    public function canPlayerAffordToSkipCard(PlayerId $playerId): bool
    {
        $costToSkip = new ResourceChanges(zeitsteineChange: -1);
        return $this->canPlayerAffordAction($playerId, $costToSkip);
    }

    public function getEventsThisTurn(): GameEvents
    {
        return $this->stream->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $this->stream->findAllAfterLastOfType(GameWasStarted::class);
    }

    public function hasPlayerSkippedACardThisRound(PlayerId $playerId): bool
    {
        $eventsThisTurn = $this->getEventsThisTurn();
        return count($eventsThisTurn->findAllOfType(CardWasSkipped::class)) > 0;
    }

    public function canPlayerAffordToActivateCard(PlayerId $player, CardDefinition $card):bool
    {
        /** @phpstan-ignore-next-line */
        return match ($card::class) {
            KategorieCardDefinition::class => $this->canPlayerAffordKategorieCard($player, $card),
            JobCardDefinition::class => $this->canPlayerAffordJobCard($player, $card),
        };
    }

    private function canPlayerAffordKategorieCard(PlayerId $player, KategorieCardDefinition $card): bool
    {
        $costToActivate = new ResourceChanges(
            zeitsteineChange: $this->hasPlayerSkippedACardThisRound($player) ? 0 : -1
        );
        return $this->canPlayerAffordAction($player, $card->resourceChanges->accumulate($costToActivate));
    }

    function canPlayerAffordJobCard(PlayerId $player, JobCardDefinition $card): bool
    {
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $player);
        if (
            $card->requirements->zeitsteine <= $playerResources->zeitsteineChange &&
            $card->requirements->bildungKompetenzsteine <= $playerResources->bildungKompetenzsteinChange &&
            $card->requirements->freizeitKompetenzsteine <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }
}
