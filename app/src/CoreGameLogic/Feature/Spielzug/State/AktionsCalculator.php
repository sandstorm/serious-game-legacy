<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\Dto\Aktion\Aktion;
use Domain\CoreGameLogic\Dto\Aktion\PhaseWechseln;
use Domain\CoreGameLogic\Dto\Aktion\ZeitsteinSetzen;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Player\State\PlayerState;
use Domain\Definitions\Cards\CardFinder;
use Domain\Definitions\Cards\Model\CardDefinition;

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
    public function canPlayerActivateCard(PlayerId $playerId, CardDefinition $card): bool
    {
        $cardRequirements = $card->requirements;
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $playerId);
        if (
            $cardRequirements->guthaben <= $playerResources->guthabenChange &&
            $cardRequirements->zeitsteine <= $playerResources->zeitsteineChange &&
            $cardRequirements->bildungKompetenzsteine <= $playerResources->bildungKompetenzsteinChange &&
            $cardRequirements->freizeitKompetenzsteine <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }

    public function canPlayerSkipCard(PlayerId $playerId, CardId $cardId): bool
    {
        // TODO implement
        return true;
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
}
