<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\InvestitionssperreModifier;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\KreditsperreModifier;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class PlayerHasFiledForInsolvenz implements GameEventInterface, Loggable, ProvidesResourceChanges, ProvidesModifiers
{
    public function __construct(
        public PlayerId        $playerId,
        public PlayerTurn      $playerTurn,
        public Year            $year,
        public ResourceChanges $resourceChanges,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            playerTurn: new PlayerTurn($values['playerTurn']),
            year: new Year($values['year']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'playerTurn' => $this->playerTurn,
            'year' => $this->year,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            playerId: $this->playerId,
            text: "hat Insolvenz angemeldet",
            resourceChanges: $this->resourceChanges,
        );
    }

    public function getModifiers(?PlayerId $playerId = null): ModifierCollection
    {
        if (!$this->playerId->equals($playerId)) { // not this player -> return empty list
            return new ModifierCollection([]);
        }

        return new ModifierCollection([
            // TODO Versicherungssperre
            // TODO GeschenkModifier
            // TODO InsolvenzAbgaben
        ]);
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }
}
