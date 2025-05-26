<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class PreGameStarted implements GameEventInterface, ProvidesResourceChanges
{
    /**
     * @param PlayerId[] $playerIds
     */
    public function __construct(
        public array $playerIds,
        public ResourceChanges $resourceChanges,
    ) {
        foreach ($this->playerIds as $playerId) {
            assert($playerId instanceof PlayerId, 'Player ID must be an instance of PlayerId');
        }
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if (in_array(needle: $playerId, haystack: $this->playerIds, strict: true)) {
            return $this->resourceChanges;
        }
        throw new \RuntimeException('Player ' . $playerId . ' does not exist', 1747827331);
    }

    public static function fromArray(array $values): GameEventInterface
    {
        $playerIds = array_map(fn (string $playerId) => PlayerId::fromString($playerId), $values['playerIds']);
        $resourceChanges = ResourceChanges::fromArray($values['resourceChanges']);
        return new self($playerIds, $resourceChanges);
    }

    public function jsonSerialize(): array
    {
        return [
            'playerIds' => $this->playerIds,
            'resourceChanges' => $this->resourceChanges->jsonSerialize(),
        ];
    }
}
