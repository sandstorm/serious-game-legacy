<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class PlayerHasFiledForInsolvenz implements GameEventInterface, Loggable, ProvidesResourceChanges
{
    public function __construct(
        protected PlayerId        $playerId,
        protected PlayerTurn      $playerTurn,
        protected Year            $year,
        protected ResourceChanges $resourceChanges,
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
            text: "hat Insolvenz angemeldet",
            playerId: $this->playerId,
            resourceChanges: $this->resourceChanges,
        );
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getYear(): Year
    {
        return $this->year;
    }
}
