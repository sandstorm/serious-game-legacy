<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Dto\Aktion\Aktion;
use Domain\CoreGameLogic\Dto\Aktion\PhaseWechseln;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\Definitions\Card\Dto\CardDefinition;
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

    /**
     * @return Aktion[]
     */
    public function availableActionsForPlayer(PlayerId $player): array
    {
        $aktionen = self::standardAktionen();
        $modifiersForPlayer = ModifierCalculator::forStream($this->stream)->forPlayer($player);
        $applicableAktionen = array_values(array_filter($aktionen, fn (Aktion $aktion) => $aktion->canExecute($player, $this->stream)));
        return $modifiersForPlayer->applyToAvailableAktionen($applicableAktionen);
    }

    // TODO maybe return an object with failed requirements?
    public function canPlayerAffordAction(PlayerId $playerId, ResourceChanges $cost): bool
    {
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $playerId);
        if (
            $cost->guthabenChange * -1 <= $playerResources->guthabenChange &&
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

    /**
     * @return Aktion[]
     */
    private static function standardAktionen(): array
    {
        return [
            new PhaseWechseln(),
            new ZeitsteinSetzen(),
        ];
    }

    public function hasPlayerSkippedACardThisRound(PlayerId $playerId): bool
    {
        $eventsThisTurn = $this->stream->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class);
        if ($eventsThisTurn === null) {
            $skipEventsThisTurn = count($this->stream->findAllAfterLastOfType(GameWasStarted::class)
                ->filter(fn ($event) => $event instanceof CardWasSkipped));
        } else {
            $skipEventsThisTurn = count($eventsThisTurn->findAllOfType(CardWasSkipped::class));
        }

        return $skipEventsThisTurn > 0;
    }

    public function canPlayerAffordToActivateCard(PlayerId $player, CardDefinition $card):bool
    {
        $costToActivate = new ResourceChanges(
            zeitsteineChange: $this->hasPlayerSkippedACardThisRound($player) ? 0 : -1
        );
        return $this->canPlayerAffordAction($player, $card->resourceChanges->accumulate($costToActivate));
    }
}
