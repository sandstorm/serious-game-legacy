<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class PlayerHasCompletedMoneysheetForCurrentKonjunkturphase implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId $playerId,
        public CurrentYear $year,
        public ResourceChanges $resourceChanges,
    ) {
    }

    /**
     * @param array<string, mixed> $values
     * @return GameEventInterface
     */
    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            year: new CurrentYear($values['year']),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            "year" => $this->year,
            "playerId" => $this->playerId,
            "resourceChanges" => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }
}
